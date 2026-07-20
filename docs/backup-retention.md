# Política de retención de backups (GDPR)

## Resumen

Este módulo complementa **Spatie Laravel Backup** sin modificar el módulo *Backup History* existente. Conserva metadatos en base de datos aunque el archivo físico se elimine, y aplica una política de retención de **90 días** para los ficheros.

## Componentes

| Componente | Ubicación |
|------------|-----------|
| Configuración de retención | `config/backup-retention.php` |
| Estrategia Spatie (oficial) | `config/backup.php` → `cleanup.default_strategy` |
| Modelo de historial | `app/Models/Backup/BackupHistoryRecord.php` |
| Servicio | `app/Services/Backup/BackupRetentionService.php` |
| Listeners (eventos) | `app/Listeners/Backup/*` |
| Scheduler | `app/Console/BackupScheduleRegistrar.php` |
| Comando de sincronización | `php artisan backup:retention-sync` |
| Tests | `tests/Feature/Backup/BackupRetentionTest.php` |

## Retención de archivos (90 días)

Configuración oficial de Spatie en `config/backup.php`:

```php
'keep_all_backups_for_days' => 90,
'keep_daily_backups_for_days' => 0,
'keep_weekly_backups_for_weeks' => 0,
'keep_monthly_backups_for_months' => 0,
'keep_yearly_backups_for_years' => 0,
```

- Los archivos **más antiguos de 90 días** los elimina `backup:clean` usando `DefaultStrategy`.
- **No** se usa `SoftDeletes` en archivos físicos: se borran del disco.
- Variable de entorno opcional: `BACKUP_RETENTION_DAYS=90`.

## Historial en base de datos

Tabla: `backup_history_records`

| Campo | Descripción |
|-------|-------------|
| `disk`, `path`, `filename`, `size_bytes` | Metadatos del backup |
| `backup_created_at` | Fecha del backup |
| `file_deleted_at` | Cuándo desapareció el archivo del disco |
| `deletion_type` | `automatic` o `manual` |
| `deleted_by_user_id` | Usuario que eliminó manualmente (nullable) |
| `audit_retention_expires_at` | Fin del periodo de auditoría |
| `anonymized_at` | Fecha de anonimización GDPR |
| `deleted_at` | Soft delete del registro de historial |

### ¿Por qué SoftDeletes solo en el historial?

- El archivo físico se elimina por completo (minimización de datos).
- El historial usa `SoftDeletes` para poder ocultar registros en consultas sin perder trazabilidad interna hasta la anonimización.

## Flujo de eventos

1. **`BackupWasSuccessful`** → registra el backup en `backup_history_records`.
2. **`CleanupWasSuccessful`** → sincroniza disco vs BD y marca eliminaciones automáticas; anonimiza auditoría caducada.
3. **Activity log `Backup deleted`** → el listener detecta eliminaciones manuales del módulo existente y registra `deleted_by_user_id` **sin modificar ese módulo**.

## Cumplimiento GDPR (UE)

| Principio | Implementación |
|-----------|----------------|
| Minimización (Art. 5.1.c) | Solo metadatos técnicos; sin contenido del backup |
| Limitación del plazo (Art. 5.1.e) | 90 días archivo / 365 días auditoría (configurable) |
| Derecho de supresión | `anonymizeExpiredAuditRecords()` elimina `deleted_by_user_id` |
| Responsabilidad proactiva | Activity Log + `backup_history_records` |

Variables de entorno:

```env
BACKUP_FILE_RETENTION_DAYS=90
BACKUP_AUDIT_RETENTION_DAYS=365
BACKUP_SCHEDULER_MUTEX=spatie-backup-pipeline
BACKUP_SCHEDULER_MUTEX_MINUTES=180
```

## Scheduler de producción

Registrado en `app/Console/Kernel.php` vía `BackupScheduleRegistrar`.

| Hora | Comando | Función |
|------|---------|---------|
| 02:00 | `backup:run` | Genera backup |
| 02:30 | `backup:clean` | Elimina backups > 90 días |
| 03:00 | `backup:monitor` | Comprueba salud de los backups |

### Explicación línea a línea (`BackupScheduleRegistrar`)

```php
$mutexName = config('backup-retention.scheduler_mutex_name');
```
Nombre compartido del mutex para que `run`, `clean` y `monitor` no se solapen.

```php
$mutexExpires = config('backup-retention.scheduler_mutex_expires_minutes');
```
Tiempo máximo (minutos) que el lock permanece activo si un proceso falla.

```php
->dailyAt('02:00')
```
Ejecuta todos los días a las 02:00 (hora de la app, `config/app.php` → `timezone`).

```php
->name($mutexName)
```
Nombre legible del evento; requerido por `onOneServer()` y útil en `schedule:list`.

```php
->createMutexNameUsing($mutexName)
```
Fuerza el mismo identificador de mutex en los tres comandos para evitar ejecuciones simultáneas.

```php
->withoutOverlapping($mutexExpires)
```
Si el mutex está ocupado, salta la ejecución. Evita dos `backup:run` a la vez.

```php
->onOneServer()
```
En entornos con varios servidores/colas, solo uno ejecuta la tarea (requiere cache compartida: Redis/DB).

```php
->runInBackground()
```
No bloquea el scheduler; lanza el comando en segundo plano.

```php
->appendOutputTo(storage_path('logs/backup-run.log'))
```
Guarda la salida para auditoría y depuración.

## Comandos Artisan de verificación

```bash
# 1. Ejecutar backup manualmente
./vendor/bin/sail artisan backup:run

# 2. Simular limpieza (elimina > 90 días)
./vendor/bin/sail artisan backup:clean

# 3. Comprobar salud de los backups
./vendor/bin/sail artisan backup:monitor

# 4. Sincronizar historial BD ↔ disco
./vendor/bin/sail artisan backup:retention-sync

# 5. Sincronizar y anonimizar auditoría caducada
./vendor/bin/sail artisan backup:retention-sync --anonymize

# 6. Ver tareas programadas
./vendor/bin/sail artisan schedule:list

# 7. Probar una tarea concreta sin esperar al cron
./vendor/bin/sail artisan schedule:test --name="spatie-backup-pipeline"

# 8. Ejecutar el scheduler una vez (útil en local)
./vendor/bin/sail artisan schedule:run

# 9. Tests del módulo
./vendor/bin/sail artisan test tests/Feature/Backup/BackupRetentionTest.php
```

## Migración

```bash
./vendor/bin/sail artisan migrate
```

Crea la tabla `backup_history_records`.

## Requisitos de producción

1. **Cron** del servidor:
   ```cron
   * * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
   ```
2. **Cache compartida** (Redis/Database) para `onOneServer()` y `withoutOverlapping()`.
3. Revisar permisos de escritura en `storage/app` y logs en `storage/logs/backup-*.log`.

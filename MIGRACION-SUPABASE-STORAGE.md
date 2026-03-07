# Migración a Supabase Storage

## Cambios Realizados

### 1. Configuración de Filesystems
- ✅ Agregado disco `supabase` en `config/filesystems.php`
- ✅ Configurado con credenciales S3 de Supabase
- ✅ Variables de entorno configuradas en `.env`

### 2. ImageHelper Actualizado
- ✅ Método `storeAndResizeLocally()` ahora acepta parámetro `$disk`
- ✅ Por defecto usa `'supabase'` en lugar de `'public'`
- ✅ Mantiene compatibilidad con storage local si se especifica

### 3. AppointmentController Modificado
- ✅ Subida de fotos ahora va a Supabase Storage
- ✅ Eliminación de fotos desde Supabase
- ✅ Cleanup automático en caso de errores

### 4. Modelo Appointment
- ✅ Agregado accessor `equipment_photo_url` para URLs públicas
- ✅ Automáticamente incluido en respuestas JSON

## Configuración de Supabase

### Credenciales Actuales (.env)
```env
AWS_ACCESS_KEY_ID=dac0ff54558db17a6526ab8c953f0d8b
AWS_SECRET_ACCESS_KEY=518c530515b9468ef836d7f3827dcbb1c74dfff15eb1aeb56db617059c9560d9
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=servispinstorage
AWS_URL=https://jvhcsdqdwhmttatqxrgg.storage.supabase.co/storage/v1/s3
AWS_ENDPOINT=https://jvhcsdqdwhmttatqxrgg.storage.supabase.co/storage/v1/s3
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### Verificar Bucket en Supabase
1. Ve a tu proyecto en Supabase Dashboard
2. Storage → Buckets
3. Verifica que existe el bucket `servispinstorage`
4. Configura políticas de acceso público si es necesario

## Políticas de Storage (RLS)

Para que las fotos sean accesibles públicamente, necesitas configurar políticas en Supabase:

```sql
-- Permitir lectura pública de fotos de appointments
CREATE POLICY "Public Access to Appointment Photos"
ON storage.objects FOR SELECT
USING (bucket_id = 'servispinstorage' AND (storage.foldername(name))[1] = 'appointment_photos');

-- Permitir subida autenticada (opcional, si usas auth)
CREATE POLICY "Authenticated users can upload appointment photos"
ON storage.objects FOR INSERT
WITH CHECK (bucket_id = 'servispinstorage' AND (storage.foldername(name))[1] = 'appointment_photos');

-- Permitir eliminación autenticada
CREATE POLICY "Authenticated users can delete appointment photos"
ON storage.objects FOR DELETE
USING (bucket_id = 'servispinstorage' AND (storage.foldername(name))[1] = 'appointment_photos');
```

## Migrar Fotos Existentes

Si tienes fotos en `storage/app/public/appointment_photos`, necesitas migrarlas:

### Opción 1: Script de Migración Manual

```php
<?php
// Ejecutar en tinker: php artisan tinker

use Illuminate\Support\Facades\Storage;
use App\Models\Appointment;

$appointments = Appointment::whereNotNull('equipment_photo_path')->get();

foreach ($appointments as $appointment) {
    $localPath = $appointment->equipment_photo_path;
    
    // Verificar si existe localmente
    if (Storage::disk('public')->exists($localPath)) {
        echo "Migrando: {$localPath}\n";
        
        // Leer contenido
        $content = Storage::disk('public')->get($localPath);
        
        // Subir a Supabase
        Storage::disk('supabase')->put($localPath, $content);
        
        // Verificar
        if (Storage::disk('supabase')->exists($localPath)) {
            echo "✓ Migrado exitosamente\n";
            
            // Opcional: eliminar local
            // Storage::disk('public')->delete($localPath);
        } else {
            echo "✗ Error al migrar\n";
        }
    }
}

echo "Migración completada\n";
```

### Opción 2: Comando Artisan (Recomendado)

Crear comando: `php artisan make:command MigratePhotosToSupabase`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Appointment;

class MigratePhotosToSupabase extends Command
{
    protected $signature = 'photos:migrate-to-supabase {--delete-local : Delete local files after migration}';
    protected $description = 'Migrate appointment photos from local storage to Supabase';

    public function handle()
    {
        $appointments = Appointment::whereNotNull('equipment_photo_path')->get();
        $this->info("Found {$appointments->count()} appointments with photos");
        
        $migrated = 0;
        $errors = 0;
        
        foreach ($appointments as $appointment) {
            $localPath = $appointment->equipment_photo_path;
            
            if (!Storage::disk('public')->exists($localPath)) {
                $this->warn("Local file not found: {$localPath}");
                continue;
            }
            
            try {
                // Leer y subir
                $content = Storage::disk('public')->get($localPath);
                Storage::disk('supabase')->put($localPath, $content);
                
                if (Storage::disk('supabase')->exists($localPath)) {
                    $this->info("✓ Migrated: {$localPath}");
                    $migrated++;
                    
                    // Eliminar local si se especificó
                    if ($this->option('delete-local')) {
                        Storage::disk('public')->delete($localPath);
                        $this->line("  Deleted local copy");
                    }
                } else {
                    throw new \Exception("Verification failed");
                }
                
            } catch (\Exception $e) {
                $this->error("✗ Error migrating {$localPath}: " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->info("\nMigration completed:");
        $this->info("  Migrated: {$migrated}");
        $this->info("  Errors: {$errors}");
        
        return 0;
    }
}
```

Ejecutar:
```bash
php artisan photos:migrate-to-supabase
# O con eliminación de archivos locales:
php artisan photos:migrate-to-supabase --delete-local
```

## Pruebas

### 1. Limpiar caché
```bash
php artisan config:clear
php artisan cache:clear
```

### 2. Ejecutar script de prueba
```bash
php artisan tinker
# Luego pegar el contenido de test-appointment-supabase.php
```

### 3. Probar API
```bash
# Crear appointment con foto (Postman/cURL)
curl -X POST http://localhost/api/appointments \
  -F "service_id=1" \
  -F "brand_id=1" \
  -F "client_first_name=Juan" \
  -F "client_last_name=Perez" \
  -F "client_email=juan@example.com" \
  -F "client_phone=+34600000000" \
  -F "start_time=2024-12-20 10:00:00" \
  -F "issue_description=Problema con lavadora" \
  -F "address=Calle Test 123" \
  -F "equipment_photo=@/path/to/photo.jpg"
```

### 4. Verificar en Supabase Dashboard
1. Ve a Storage → servispinstorage
2. Busca la carpeta `appointment_photos`
3. Verifica que las fotos se suben correctamente

## URLs Públicas

Las URLs ahora se generan automáticamente:

```php
$appointment = Appointment::find(1);
echo $appointment->equipment_photo_url;
// https://jvhcsdqdwhmttatqxrgg.storage.supabase.co/storage/v1/s3/servispinstorage/appointment_photos/20241220120000_abc123.jpg
```

## Troubleshooting

### Error: "Access Denied"
- Verifica las políticas RLS en Supabase Storage
- Asegúrate de que el bucket sea público o tenga las políticas correctas

### Error: "Bucket not found"
- Verifica que el bucket `servispinstorage` existe en Supabase
- Revisa la variable `AWS_BUCKET` en `.env`

### Error: "Invalid credentials"
- Regenera las credenciales S3 en Supabase Dashboard
- Actualiza `AWS_ACCESS_KEY_ID` y `AWS_SECRET_ACCESS_KEY` en `.env`

### Las URLs no funcionan
- Verifica que `AWS_URL` apunta al endpoint correcto
- Asegúrate de que las políticas de lectura pública están configuradas

## Rollback (si es necesario)

Para volver al storage local:

1. En `ImageHelper.php`, cambiar el default:
```php
public static function storeAndResizeLocally($image, $storagePath, $disk = 'public')
```

2. En `AppointmentController.php`, cambiar todas las referencias:
```php
Storage::disk('public')->...
```

3. Limpiar caché:
```bash
php artisan config:clear
```

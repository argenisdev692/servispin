<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retención de archivos de backup (GDPR – minimización de datos)
    |--------------------------------------------------------------------------
    |
    | Los archivos físicos se eliminan automáticamente según la estrategia
    | oficial de Spatie en config/backup.php. Este valor documenta la política
    | y se usa para calcular audit_retention_expires_at en el historial.
    |
    */
    'file_retention_days' => (int) env('BACKUP_FILE_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Retención del historial de auditoría (GDPR – Art. 5.1.e)
    |--------------------------------------------------------------------------
    |
    | Tras eliminar el archivo, conservamos metadatos de auditoría durante este
    | periodo. Pasado el plazo, el servicio anonimiza deleted_by_user_id.
    |
    */
    'audit_retention_days' => (int) env('BACKUP_AUDIT_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Mutex compartido del scheduler
    |--------------------------------------------------------------------------
    |
    | Nombre del mutex para evitar solapamiento entre backup:run, backup:clean
    | y backup:monitor cuando se ejecutan en la misma ventana nocturna.
    |
    */
    'scheduler_mutex_name' => env('BACKUP_SCHEDULER_MUTEX', 'spatie-backup-pipeline'),

    /*
    |--------------------------------------------------------------------------
    | Tiempo máximo de bloqueo (minutos)
    |--------------------------------------------------------------------------
    */
    'scheduler_mutex_expires_minutes' => (int) env('BACKUP_SCHEDULER_MUTEX_MINUTES', 180),

];

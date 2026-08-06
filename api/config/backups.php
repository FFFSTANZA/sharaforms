<?php

return [
    'enabled' => env('BACKUP_ENABLED', true),
    'disk' => env('BACKUP_DISK', 'local'),
    'path' => trim((string) env('BACKUP_PATH', 'backups/database'), '/'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
    'compress' => env('BACKUP_COMPRESS', true),
    'schedule' => env('BACKUP_SCHEDULE', '02:30'),
];

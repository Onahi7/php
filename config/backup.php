<?php

return [
    // Backup storage configuration
    'backup_path' => __DIR__ . '/../storage/backups',
    
    // Retention policy
    'retention_days' => 30,
    
    // Paths to exclude from backup
    'exclude_paths' => [
        __DIR__ . '/../storage/logs',
        __DIR__ . '/../storage/cache',
        __DIR__ . '/../storage/backups',
        __DIR__ . '/../vendor',
        __DIR__ . '/../node_modules'
    ],
    
    // Remote storage configuration
    'remote_storage' => [
        'enabled' => false,
        'type' => 's3', // 's3' or 'ftp'
        
        // S3 configuration
        's3' => [
            'bucket' => 'your-bucket-name',
            'region' => 'your-region',
            'access_key' => 'your-access-key',
            'secret_key' => 'your-secret-key',
            'path' => 'backups/'
        ],
        
        // FTP configuration
        'ftp' => [
            'host' => 'ftp.example.com',
            'username' => 'your-username',
            'password' => 'your-password',
            'path' => '/backups/'
        ]
    ],
    
    // Backup schedule
    'schedule' => [
        'database' => [
            'frequency' => 'daily',
            'time' => '00:00'
        ],
        'files' => [
            'frequency' => 'weekly',
            'day' => 'sunday',
            'time' => '01:00'
        ]
    ],
    
    // Notification settings
    'notifications' => [
        'email' => [
            'enabled' => true,
            'recipients' => ['admin@example.com'],
            'events' => ['success', 'failure']
        ]
    ]
];

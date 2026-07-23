<?php

$appUrl = env('APP_URL');

if (! $appUrl && env('RAILWAY_PUBLIC_DOMAIN')) {
    $appUrl = 'https://'.env('RAILWAY_PUBLIC_DOMAIN');
}

$volumeMountPath = env('RAILWAY_VOLUME_MOUNT_PATH');
$publicDiskRoot = env('PUBLIC_DISK_ROOT');
$localDiskRoot = env('LOCAL_DISK_ROOT');

if (! $publicDiskRoot && $volumeMountPath) {
    $publicDiskRoot = rtrim($volumeMountPath, '/').'/app/public';
}

if (! $localDiskRoot && $volumeMountPath) {
    $localDiskRoot = rtrim($volumeMountPath, '/').'/app/private';
}

$publicDiskRoot = $publicDiskRoot ?: storage_path('app/public');
$localDiskRoot = $localDiskRoot ?: storage_path('app/private');

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => $localDiskRoot,
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => $publicDiskRoot,
            'url' => rtrim($appUrl ?: 'http://localhost', '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => $publicDiskRoot,
    ],

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Livewire Component Namespace / Views
    |--------------------------------------------------------------------------
    */

    'class_namespace' => 'App\\Livewire',

    'view_path' => resource_path('views/livewire'),

    'layout' => 'components.layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Livewire Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Filament FileUpload fields attach the file before the final form submit.
    | These values keep temporary uploads on the local private disk, increase
    | the validation limit to match the Docker/PHP upload limits, and retain
    | Livewire's upload throttling middleware.
    |
    */

  'temporary_file_upload' => [
    'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', 'local'),

    'rules' => env(
        'LIVEWIRE_TEMPORARY_FILE_UPLOAD_RULES',
        'file|max:30720'
    ),

    'directory' => env(
        'LIVEWIRE_TEMPORARY_FILE_UPLOAD_DIRECTORY',
        'livewire-tmp'
    ),

    'middleware' => [
        'throttle:60,1',
    ],

    'preview_mimes' => [
        'png',
        'gif',
        'bmp',
        'svg',
        'wav',
        'mp4',
        'mov',
        'avi',
        'wmv',
        'mp3',
        'm4a',
        'jpg',
        'jpeg',
        'mpga',
        'webp',
        'wma',
    ],

    'max_upload_time' => 5,
],

    'render_on_redirect' => false,

    'legacy_model_binding' => false,

    'inject_assets' => true,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#10b981',
    ],

    'inject_morph_markers' => true,

    'pagination_theme' => 'tailwind',
];

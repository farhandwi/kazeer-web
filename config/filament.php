<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | By uncommenting the Laravel Echo configuration, you may connect Filament
    | to any Pusher-compatible websockets server.
    |
    | This will allow your users to receive real-time notifications.
    |
    */

    'broadcasting' => [

        // 'echo' => [
        //     'broadcaster' => 'pusher',
        //     'key' => env('VITE_PUSHER_APP_KEY'),
        //     'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
        //     'wsHost' => env('VITE_PUSHER_HOST'),
        //     'wsPort' => env('VITE_PUSHER_PORT'),
        //     'wssPort' => env('VITE_PUSHER_PORT'),
        //     'authEndpoint' => '/broadcasting/auth',
        //     'disableStats' => true,
        //     'encrypted' => true,
        //     'forceTLS' => true,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk Filament will use to store files. You may use
    | any of the disks defined in the `config/filesystems.php`.
    |
    */

    'default_filesystem_disk' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where Filament's assets will be published to. It
    | is relative to the `public` directory of your Laravel application.
    |
    | After changing the path, you should run `php artisan filament:assets`.
    |
    */

    'assets_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | This is the directory that Filament will use to store cache files that
    | are used to optimize the registration of components.
    |
    | After changing the path, you should run `php artisan filament:cache-components`.
    |
    */

    'cache_path' => base_path('bootstrap/cache/filament'),

    /*
    |--------------------------------------------------------------------------
    | Livewire Loading Delay
    |--------------------------------------------------------------------------
    |
    | This sets the delay before loading indicators appear.
    |
    | Setting this to 'none' makes indicators appear immediately, which can be
    | desirable for high-latency connections. Setting it to 'default' applies
    | Livewire's standard 200ms delay.
    |
    */

    'livewire_loading_delay' => 'default',

    /*
    |--------------------------------------------------------------------------
    | File Generation
    |--------------------------------------------------------------------------
    |
    | Artisan commands that generate files can be configured here by setting
    | configuration flags that will impact their location or content.
    |
    | Often, this is useful to preserve file generation behavior from a
    | previous version of Filament, to ensure consistency between older and
    | newer generated files. These flags are often documented in the upgrade
    | guide for the version of Filament you are upgrading to.
    |
    */

    'file_generation' => [
        'flags' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the prefix used for the system routes that Filament registers,
    | such as the routes for downloading exports and failed import rows.
    |
    */

    'system_route_prefix' => 'filament',

    'order_statuses' => [
        'pending' => [
            'label' => 'Pending',
            'color' => 'gray',
            'description' => 'Order has been placed but not yet confirmed',
        ],
        'confirmed' => [
            'label' => 'Confirmed', 
            'color' => 'warning',
            'description' => 'Order has been confirmed and will be prepared',
        ],
        'preparing' => [
            'label' => 'Preparing',
            'color' => 'info', 
            'description' => 'Kitchen is preparing the order',
        ],
        'ready' => [
            'label' => 'Ready',
            'color' => 'success',
            'description' => 'Order is ready to be served',
        ],
        'served' => [
            'label' => 'Served',
            'color' => 'success',
            'description' => 'Order has been served to customer',
        ],
        'completed' => [
            'label' => 'Completed',
            'color' => 'success', 
            'description' => 'Order is complete and customer is satisfied',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'color' => 'danger',
            'description' => 'Order has been cancelled',
        ],
    ],
    
    'payment_statuses' => [
        'pending' => [
            'label' => 'Pending',
            'color' => 'gray',
        ],
        'paid' => [
            'label' => 'Paid',
            'color' => 'success',
        ],
        'failed' => [
            'label' => 'Failed',
            'color' => 'danger',
        ],
        'refunded' => [
            'label' => 'Refunded',
            'color' => 'warning',
        ],
    ],
    
    'spice_levels' => [
        'none' => [
            'label' => 'No Spice',
            'color' => 'gray',
            'icon' => '🌿',
        ],
        'mild' => [
            'label' => 'Mild',
            'color' => 'success',
            'icon' => '🌶️',
        ],
        'medium' => [
            'label' => 'Medium',
            'color' => 'warning', 
            'icon' => '🌶️🌶️',
        ],
        'hot' => [
            'label' => 'Hot',
            'color' => 'danger',
            'icon' => '🌶️🌶️🌶️',
        ],
        'very_hot' => [
            'label' => 'Very Hot',
            'color' => 'danger',
            'icon' => '🌶️🌶️🌶️🔥',
        ],
    ],

];

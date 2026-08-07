<?php

namespace App\Providers;

use App\Http\View\Composers\MenuComposer;
use App\Models\Menu;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDriveService;
use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        /* =========================================================================
         * 1. 🌿 FRONTEND MENU COMPOSER & CACHE LAYER
         * ========================================================================= */
        View::composer('*', function ($view) {
            $navigationTree = Cache::remember('frontend_navigation_tree', now()->addDays(7), function () {
                return Menu::whereNull('parent_id')
                    ->where('is_active', true)
                    ->with(['children' => function ($query) {
                        $query->where('is_active', true)->orderBy('sort_order', 'asc');
                    }])
                    ->orderBy('sort_order', 'asc')
                    ->get();
            });

            $view->with('navigationTree', $navigationTree);
        });

        View::composer('layouts.frontend', MenuComposer::class);

        /*
        |--------------------------------------------------------------------------
        | ☁️ Register Google Drive Storage Driver (OAuth 2.0 Engine)
        |--------------------------------------------------------------------------
        */
        try {
            Storage::extend('google', function ($app, $config) {
                $clientId = $config['client_id'] ?? env('GOOGLE_DRIVE_CLIENT_ID');
                $clientSecret = $config['client_secret'] ?? env('GOOGLE_DRIVE_CLIENT_SECRET');
                $refreshToken = $config['refresh_token'] ?? env('GOOGLE_DRIVE_REFRESH_TOKEN');
                $folderId = $config['folder_id'] ?? env('GOOGLE_DRIVE_FOLDER_ID');

                if (empty($clientId) || empty($clientSecret) || empty($refreshToken)) {
                    throw new Exception("ไม่พบการตั้งค่า OAuth 2.0 (Client ID / Secret / Refresh Token) ในไฟล์ .env");
                }

                $client = new GoogleClient();
                $client->setClientId($clientId);
                $client->setClientSecret($clientSecret);
                $client->refreshToken($refreshToken);
                $client->addScope(GoogleDriveService::DRIVE);

                $service = new GoogleDriveService($client);
                $options = [];

                if (!empty($config['teamDriveId'])) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                $adapter = new GoogleDriveAdapter($service, $folderId, $options);

                return new Filesystem($adapter);
            });
        } catch (Exception $e) {
            logger()->error('🚨 [Google Drive Driver Error] ' . $e->getMessage());
        }
    }
}
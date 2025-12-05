<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ladmin\AdminServiceProvider;
use Ladmin\Facades\Admin;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $baseUrl = 'http://localhost:8000';

    /**
     * Cache heavy one-time bootstrapping across tests.
     */
    protected static bool $publishedAssets = false;

    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Admin', Admin::class);
        });

        $app->make(Kernel::class)->bootstrap();

        $app->register(AdminServiceProvider::class);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $adminConfig = require __DIR__ . '/config/admin.php';

        // Use sqlite in-memory for tests to avoid external MySQL dependency
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $this->app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $this->app['config']->set('filesystems', require __DIR__ . '/config/filesystems.php');
        $this->app['config']->set('admin', $adminConfig);

        foreach (Arr::dot(Arr::get($adminConfig, 'auth'), 'auth.') as $key => $value) {
            $this->app['config']->set($key, $value);
        }

        // Publish assets only once across the entire test run to avoid repeated heavy I/O
        if (!self::$publishedAssets) {
            $this->artisan('vendor:publish', ['--provider' => 'Ladmin\AdminServiceProvider']);
            self::$publishedAssets = true;
        }

        Schema::defaultStringLength(191);

        $this->artisan('admin:install');

        $this->migrateTestTables();

        if (file_exists($routes = admin_path('routes.php'))) {
            require $routes;
        }

        require __DIR__ . '/routes.php';

        require_once __DIR__ . '/seeds/factory.php';

        //        \Ladmin\Admin::$css = [];
        //        \Ladmin\Admin::$js = [];
        //        \Ladmin\Admin::$script = [];
    }

    protected function tearDown(): void
    {
        (new CreateAdminTables())->down();

        (new CreateTestTables())->down();

        DB::select("delete from `migrations` where `migration` = '2016_01_04_173148_create_admin_tables'");

        parent::tearDown();
    }

    /**
     * run package database migrations.
     *
     * @return void
     */
    public function migrateTestTables()
    {
        $fileSystem = new Filesystem();

        $fileSystem->requireOnce(__DIR__ . '/migrations/2016_11_22_093148_create_test_tables.php');

        (new CreateTestTables())->up();
    }
}

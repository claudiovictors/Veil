<?php

/*
|--------------------------------------------------------------------------
| Veil Install Command
|--------------------------------------------------------------------------
|
| This command handles the full installation of the Slenix Veil 
| authentication scaffolding. It automates the deployment of the User model, 
| controllers, middlewares, views, and multiple database migrations 
| while also injecting necessary routes into the application.
|
*/

declare(strict_types=1);

namespace Slenix\Veil\Console;

use Slenix\Core\Console\Command;
use Slenix\Veil\VeilServiceProvider;

class VeilInstallCommand extends Command
{
    /** @var array The raw command line arguments. */
    private array  $args;

    /** @var bool Whether to overwrite existing files via the --force flag. */
    private bool   $force;

    /** @var string The root directory of the project. */
    private string $projectRoot;

    /** @var string The source path for template stubs. */
    private string $stubsPath;

    /**
     * VeilInstallCommand constructor.
     * * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args        = $args;
        $this->force       = in_array('--force', $args, true);
        $this->projectRoot = dirname(__DIR__, 5);
        $this->stubsPath   = VeilServiceProvider::stubsPath();
    }

    /**
     * Run the installation process.
     * * @return void
     */
    public function install(): void
    {
        self::newLine();
        self::info('Installing Slenix Veil authentication scaffolding...');
        self::newLine();

        $this->publishModel();
        $this->publishController();
        $this->publishMiddlewares();
        $this->publishViews();
        $this->publishMigrations();
        $this->appendRoutes();

        self::newLine();
        self::success('Veil installed successfully!');
        self::newLine();
        self::info('Next steps:');
        echo "  1. Run: php celestial migrate" . PHP_EOL;
        echo "  2. Visit /login or /register in your browser." . PHP_EOL;
        self::newLine();
    }

    /**
     * Publishes the User model stub to the application.
     * * @return void
     */
    private function publishModel(): void
    {
        $this->publish(
            $this->stubsPath . '/models/User.stub',
            $this->projectRoot . '/app/Models/User.php',
            'User model'
        );
    }

    /**
     * Publishes the AuthController stub.
     * * @return void
     */
    private function publishController(): void
    {
        $this->publish(
            $this->stubsPath . '/controllers/AuthController.stub',
            $this->projectRoot . '/app/Controllers/AuthController.php',
            'AuthController'
        );
    }

    /**
     * Publishes the Auth and Guest middleware stubs.
     * * @return void
     */
    private function publishMiddlewares(): void
    {
        $middlewares = [
            'AuthMiddleware'  => 'middlewares/AuthMiddleware.stub',
            'GuestMiddleware' => 'middlewares/GuestMiddleware.stub',
        ];

        foreach ($middlewares as $name => $stubFile) {
            $this->publish(
                $this->stubsPath . '/' . $stubFile,
                $this->projectRoot . '/app/Middlewares/' . $name . '.php',
                $name
            );
        }
    }

    /**
     * Publishes all Luna view stubs for authentication.
     * * @return void
     */
    private function publishViews(): void
    {
        $views = [
            'auth/layout.luna.php'    => 'views/layout.stub',
            'auth/login.luna.php'     => 'views/login.stub',
            'auth/register.luna.php'  => 'views/register.stub',
            'auth/dashboard.luna.php' => 'views/dashboard.stub',
            'auth/settings.luna.php'  => 'views/settings.stub',
        ];

        foreach ($views as $relative => $stubFile) {
            $this->publish(
                $this->stubsPath . '/' . $stubFile,
                $this->projectRoot . '/views/' . $relative,
                $relative
            );
        }
    }

    /**
     * Publishes multiple database migrations in sequential order.
     * * @return void
     */
    private function publishMigrations(): void
    {
        // Order matters — users first, then dependents
        $migrations = [
            'create_users_table'              => '01',
            'create_remember_tokens_table'    => '02',
            'create_password_resets_table'    => '03',
            'create_roles_permissions_tables' => '04',
        ];

        $base = date('Y_m_d_His');

        foreach ($migrations as $name => $suffix) {
            $timestamp   = $base . $suffix;
            $destination = $this->projectRoot . '/database/migrations/' . $timestamp . '_' . $name . '.php';

            $this->publish(
                $this->stubsPath . '/migrations/' . $name . '.stub',
                $destination,
                $name . ' migration'
            );
        }
    }

    /**
     * Appends Veil routes to the web.php file if they aren't already present.
     * * @return void
     */
    private function appendRoutes(): void
    {
        $routesFile = $this->projectRoot . '/routes/web.php';
        $stub       = $this->stubsPath . '/routes.stub';
        $marker     = '// @veil-routes';

        if (!file_exists($routesFile)) {
            self::warning("routes/web.php not found. Skipping route injection.");
            return;
        }

        if (!file_exists($stub)) {
            self::warning("Routes stub not found. Skipping.");
            return;
        }

        $routesContent = file_get_contents($routesFile);

        if (str_contains($routesContent, $marker)) {
            self::warning("Veil routes already present in web.php. Skipping.");
            return;
        }

        file_put_contents($routesFile, $routesContent . PHP_EOL . file_get_contents($stub));
        self::success("Routes appended → routes/web.php");
    }

    /**
     * Copies a stub file to a destination while handling directory creation.
     * * @param string $stub The path to the source stub.
     * @param string $destination The path to the target destination.
     * @param string $label The label used for console output.
     * @return void
     */
    private function publish(string $stub, string $destination, string $label): void
    {
        if (!file_exists($stub)) {
            self::error("Stub not found: {$stub}");
            return;
        }

        if (file_exists($destination) && !$this->force) {
            self::warning("Already exists (use --force to overwrite): {$label}");
            return;
        }

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (copy($stub, $destination)) {
            self::success("Published → " . str_replace($this->projectRoot . '/', '', $destination));
        } else {
            self::error("Failed to publish: {$label}");
        }
    }
}
<?php

/*
|--------------------------------------------------------------------------
| Veil Install Command
|--------------------------------------------------------------------------
|
| This command handles the full installation of the Slenix Veil
| authentication scaffolding. It automates the deployment of the User model,
| controllers, form requests, middlewares, views, CSS assets, logo, and
| database migrations, and injects the necessary routes into web.php.
|
| Usage:
|   php celestial veil:install
|   php celestial veil:install --force   # Overwrite existing files
|
*/

declare(strict_types=1);

namespace Slenix\Veil\Console;

use Slenix\Core\Console\Command;
use Slenix\Veil\VeilServiceProvider;

/**
 * VeilInstallCommand
 *
 * Orchestrates the full Veil authentication scaffolding installation.
 * Publishes stubs for models, controllers, form requests, middlewares,
 * views, CSS assets, the Veil logo, and injects authentication routes
 * into the project's web.php file.
 *
 * @version 1.4.0
 */
class VeilInstallCommand extends Command
{
    /**
     * The raw command-line arguments passed to the command.
     *
     * @var array<int, string>
     */
    private array $args;

    /**
     * Whether existing files should be overwritten.
     * Controlled by the --force flag.
     *
     * @var bool
     */
    private bool $force;

    /**
     * The absolute path to the root of the host project.
     *
     * @var string
     */
    private string $projectRoot;

    /**
     * The absolute path to the Veil stubs directory.
     *
     * @var string
     */
    private string $stubsPath;

    /**
     * VeilInstallCommand constructor.
     *
     * Resolves the project root and stubs path, and checks for the
     * --force flag in the provided arguments.
     *
     * @param array<int, string> $args Raw CLI arguments.
     */
    public function __construct(array $args)
    {
        $this->args = $args;
        $this->force = in_array('--force', $args, true);
        $this->projectRoot = dirname(__DIR__, 5);
        $this->stubsPath = VeilServiceProvider::stubsPath();
    }

    /**
     * Execute the full Veil installation process.
     *
     * Runs each publishing step in the correct dependency order and
     * appends authentication routes to the project's web.php file.
     *
     * @return void
     */
    public function install(): void
    {
        self::newLine();
        self::info('Installing Slenix Veil authentication scaffolding...');
        self::newLine();

        $this->publishControllers();
        $this->publishFormRequests();
        $this->publishMiddlewares();
        $this->publishViews();
        $this->publishAssets();
        $this->appendRoutes();

        self::newLine();
        self::success('Veil installed successfully!');
        self::newLine();
        self::info('Next steps:');
        echo '  1. Run: php celestial migrate' . PHP_EOL;
        echo '  2. Visit /login or /register in your browser.' . PHP_EOL;
        self::newLine();
    }

    /**
     * Publish the User model stub.
     *
     * src/Stubs/model/User.stub → app/Models/User.php
     *
     * @return void
     */
    private function publishModel(): void
    {
        $this->publish(
            $this->stubsPath . '/model/User.stub',
            $this->projectRoot . '/app/Models/User.php',
            'User model'
        );
    }

    /**
     * Publish all controller stubs.
     *
     * src/Stubs/controllers/AuthController.stub      → app/Controllers/AuthController.php
     * src/Stubs/controllers/DashboardController.stub → app/Controllers/DashboardController.php
     *
     * @return void
     */
    private function publishControllers(): void
    {
        $controllers = [
            'AuthController' => 'controllers/AuthController.stub',
            'DashboardController' => 'controllers/DashboardController.stub',
        ];

        foreach ($controllers as $name => $stubFile) {
            $this->publish(
                $this->stubsPath . '/' . $stubFile,
                $this->projectRoot . '/app/Controllers/' . $name . '.php',
                $name
            );
        }
    }

    /**
     * Publish all Form Request stubs.
     *
     * src/Stubs/Http/Requests/LoginRequest.stub    → app/Http/Requests/LoginRequest.php
     * src/Stubs/Http/Requests/RegisterRequest.stub → app/Http/Requests/RegisterRequest.php
     *
     * @return void
     */
    private function publishFormRequests(): void
    {
        $requests = [
            'LoginRequest' => 'Http/Requests/LoginRequest.stub',
            'RegisterRequest' => 'Http/Requests/RegisterRequest.stub',
        ];

        foreach ($requests as $name => $stubFile) {
            $this->publish(
                $this->stubsPath . '/' . $stubFile,
                $this->projectRoot . '/app/Http/Requests/' . $name . '.php',
                $name
            );
        }
    }

    /**
     * Publish the Auth and Guest middleware stubs.
     *
     * src/Stubs/middlewares/AuthMiddleware.stub  → app/Middlewares/AuthMiddleware.php
     * src/Stubs/middlewares/GuestMiddleware.stub → app/Middlewares/GuestMiddleware.php
     *
     * @return void
     */
    private function publishMiddlewares(): void
    {
        $middlewares = [
            'AuthMiddleware' => 'middlewares/AuthMiddleware.stub',
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
     * Publish all Luna view stubs for authentication scaffolding.
     *
     * src/Stubs/views/app.stub       → views/layouts/app.luna.php
     * src/Stubs/views/guest.stub     → views/layouts/guest.luna.php
     * src/Stubs/views/login.stub     → views/auth/login.luna.php
     * src/Stubs/views/register.stub  → views/auth/register.luna.php
     * src/Stubs/views/index.stub     → views/dashboard/index.luna.php
     *
     * @return void
     */
    private function publishViews(): void
    {
        $views = [
            'layouts/app.luna.php' => 'views/app.stub',
            'layouts/guest.luna.php' => 'views/guest.stub',
            'auth/login.luna.php' => 'views/login.stub',
            'auth/register.luna.php' => 'views/register.stub',
            'dashboard/index.luna.php' => 'views/index.stub',
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
     * Publish Veil's CSS stylesheets and logo to the project's public directory.
     *
     * Stylesheets:
     *   src/Stubs/css/style.css → public/css/style.css
     *   src/Stubs/css/auth.css  → public/css/auth.css
     *
     * Logo (replaces the default Slenix logo):
     *   src/Stubs/public/logo.png → public/logo.png
     *
     * @return void
     */
    private function publishAssets(): void
    {
        // CSS files
        $stylesheets = [
            'style.css' => 'css/style.css',
            'auth.css' => 'css/auth.css',
        ];

        foreach ($stylesheets as $filename => $stubFile) {
            $this->publish(
                $this->stubsPath . '/' . $stubFile,
                $this->projectRoot . '/public/css/' . $filename,
                'public/css/' . $filename
            );
        }

        // Veil logo — replaces the default Slenix logo
        $this->publish(
            $this->stubsPath . '/public/logo.png',
            $this->projectRoot . '/public/logo.png',
            'public/logo.png'
        );
    }

    /**
     * Publish database migration stubs in sequential order.
     *
     * Each filename is prefixed with a timestamp to guarantee correct
     * execution order when running `php celestial migrate`.
     *
     * create_users_table → database/migrations/{timestamp}01_create_users_table.php
     *
     * @return void
     */
    private function publishMigrations(): void
    {
        $migrations = [
            'create_users_table' => '01',
        ];

        $base = date('Y_m_d_His');

        foreach ($migrations as $name => $suffix) {
            $timestamp = $base . $suffix;
            $destination = $this->projectRoot . '/database/migrations/' . $timestamp . '_' . $name . '.php';

            $this->publish(
                $this->stubsPath . '/migrations/' . $name . '.stub',
                $destination,
                $name . ' migration'
            );
        }
    }

    /**
     * Append Veil's authentication routes to the project's web.php file.
     *
     * Uses the `// @veil-routes` marker to detect whether routes have
     * already been injected, preventing duplicate entries on repeated runs.
     *
     * @return void
     */
    private function appendRoutes(): void
    {
        $routesFile = $this->projectRoot . '/routes/web.php';
        $stub = $this->stubsPath . '/routes.stub';
        $marker = '// @veil-routes';

        if (!file_exists($routesFile)) {
            self::warning('routes/web.php not found. Skipping route injection.');
            return;
        }

        if (!file_exists($stub)) {
            self::warning('Routes stub not found. Skipping route injection.');
            return;
        }

        $routesContent = file_get_contents($routesFile);

        if (str_contains($routesContent, $marker)) {
            self::warning('Veil routes already present in web.php. Skipping.');
            return;
        }

        file_put_contents($routesFile, $routesContent . PHP_EOL . file_get_contents($stub));
        self::success('Routes appended → routes/web.php');
    }

    /**
     * Copy a stub file to its destination in the host project.
     *
     * Creates any missing intermediate directories automatically.
     * Skips the copy if the destination already exists and --force
     * was not passed, preserving any user modifications.
     *
     * @param string $stub        Absolute path to the source stub file.
     * @param string $destination Absolute path to the target file.
     * @param string $label       Human-readable label used in console output.
     *
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

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            self::error("Failed to create directory: {$dir}");
            return;
        }

        if (copy($stub, $destination)) {
            self::success('Published → ' . str_replace($this->projectRoot . '/', '', $destination));
        } else {
            self::error("Failed to publish: {$label}");
        }
    }
}
<?php

/*
|--------------------------------------------------------------------------
| Veil Install Command
|--------------------------------------------------------------------------
|
| This command handles the full installation of the Slenix Veil
| authentication scaffolding. It automates the deployment of the User model,
| controllers, middlewares, views, and database migrations while also
| injecting the necessary routes into the application's route file.
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
 * Publishes stubs for models, controllers, middlewares, views, and
 * migrations, and injects authentication routes into web.php.
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
        $this->args        = $args;
        $this->force       = in_array('--force', $args, true);
        $this->projectRoot = dirname(__DIR__, 5);
        $this->stubsPath   = VeilServiceProvider::stubsPath();
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

        $this->publishModel();
        $this->publishControllers();
        $this->publishMiddlewares();
        $this->publishViews();
        $this->publishAssets();
        $this->publishMigrations();
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
     * Publish the User Eloquent model stub.
     *
     * Copies src/Stubs/model/User.stub → app/Models/User.php.
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
     * Publish all controller stubs to the application.
     *
     * Copies:
     *   - src/Stubs/controllers/AuthController.stub      → app/Controllers/AuthController.php
     *   - src/Stubs/controllers/DashboardController.stub → app/Controllers/DashboardController.php
     *
     * @return void
     */
    private function publishControllers(): void
    {
        $controllers = [
            'AuthController'      => 'controllers/AuthController.stub',
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
     * Publish the Auth and Guest middleware stubs.
     *
     * Copies:
     *   - src/Stubs/middlewares/AuthMiddleware.stub  → app/Middlewares/AuthMiddleware.php
     *   - src/Stubs/middlewares/GuestMiddleware.stub → app/Middlewares/GuestMiddleware.php
     *
     * @return void
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
     * Publish all Luna view stubs for authentication scaffolding.
     *
     * Before publishing the rest of the views, the project's default
     * welcome.luna.php is replaced with Veil's own welcome stub so the
     * application root URL reflects the new landing page immediately.
     *
     * Stub → destination mapping:
     *   - views/welcome.stub   → views/welcome.luna.php          (replaces existing)
     *   - views/app.stub       → views/layouts/app.luna.php
     *   - views/guest.stub     → views/layouts/guest.luna.php
     *   - views/login.stub     → views/auth/login.luna.php
     *   - views/register.stub  → views/auth/register.luna.php
     *   - views/dashboard.stub → views/dashboard/index.luna.php
     *   - views/setthings.stub → views/dashboard/settings.luna.php
     *
     * @return void
     */
    private function publishViews(): void
    {
        $this->replaceWelcomeView();

        $views = [
            'layouts/app.luna.php'         => 'views/app.stub',
            'layouts/guest.luna.php'       => 'views/guest.stub',
            'auth/login.luna.php'          => 'views/login.stub',
            'auth/register.luna.php'       => 'views/register.stub',
            'dashboard/index.luna.php'     => 'views/dashboard.stub',
            'dashboard/settings.luna.php'  => 'views/setthings.stub',
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
     * Replace the project's default welcome view with Veil's welcome stub.
     *
     * Deletes views/welcome.luna.php if it exists, then publishes
     * src/Stubs/views/welcome.stub in its place. This ensures the
     * application's root URL serves Veil's landing page after installation.
     *
     * @return void
     */
    private function replaceWelcomeView(): void
    {
        $welcomeDestination = $this->projectRoot . '/views/welcome.luna.php';
        $welcomeStub        = $this->stubsPath . '/views/welcome.stub';

        // Remove the existing welcome view so publish() always writes fresh.
        if (file_exists($welcomeDestination)) {
            if (unlink($welcomeDestination)) {
                self::info('Removed existing welcome view → views/welcome.luna.php');
            } else {
                self::warning('Could not remove existing welcome view. Proceeding anyway.');
            }
        }

        $this->publish($welcomeStub, $welcomeDestination, 'welcome.luna.php');
    }


    /**
     * Publish Veil's CSS asset files to the project's public directory.
     *
     * Copies the bundled stylesheets from src/Stubs/css/ into public/css/
     * so they are immediately accessible by the browser. Existing files are
     * only overwritten when the --force flag is provided.
     *
     * Stub → destination mapping:
     *   - css/style.css → public/css/style.css
     *   - css/auth.css  → public/css/auth.css
     *
     * @return void
     */
    private function publishAssets(): void
    {
        $assets = [
            'style.css' => 'css/style.css',
            'auth.css'  => 'css/auth.css',
        ];

        foreach ($assets as $filename => $stubFile) {
            $this->publish(
                $this->stubsPath . '/' . $stubFile,
                $this->projectRoot . '/public/css/' . $filename,
                'public/css/' . $filename
            );
        }
    }

    /**
     * Publish database migration stubs in sequential order.
     *
     * Each migration filename is prefixed with a timestamped suffix to
     * guarantee correct execution order when running `php celestial migrate`.
     *
     * Currently published migrations:
     *   - create_users_table
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
     * Append Veil's authentication routes to the project's web.php file.
     *
     * Uses a marker comment (`// @veil-routes`) to detect whether the
     * routes have already been injected, preventing duplicate entries on
     * repeated installations.
     *
     * @return void
     */
    private function appendRoutes(): void
    {
        $routesFile = $this->projectRoot . '/routes/web.php';
        $stub       = $this->stubsPath . '/routes.stub';
        $marker     = '// @veil-routes';

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
     * was not passed, preserving user modifications.
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
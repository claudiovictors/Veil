<?php

/*
|--------------------------------------------------------------------------
| Veil Install Command
|--------------------------------------------------------------------------
|
| This command handles the installation of the Veil authentication 
| scaffolding into a Slenix project. It automates the publishing of 
| controllers, middlewares, views, and migrations.
|
*/

declare(strict_types=1);

namespace Slenix\Veil\Console;

use Slenix\Core\Console\Command;
use Slenix\Veil\VeilServiceProvider;

class VeilInstallCommand extends Command
{
    /** @var array The command line arguments. */
    private array  $args;

    /** @var bool Flag to determine if existing files should be overwritten. */
    private bool   $force;

    /** @var string The absolute path to the project root. */
    private string $projectRoot;

    /** @var string The absolute path to the package stubs. */
    private string $stubsPath;

    /**
     * VeilInstallCommand constructor.
     *
     * @param array $args Command line arguments.
     */
    public function __construct(array $args)
    {
        $this->args        = $args;
        $this->force       = in_array('--force', $args, true);
        $this->projectRoot = dirname(__DIR__, 5);
        $this->stubsPath   = VeilServiceProvider::stubsPath();
    }

    /**
     * Execute the installation process.
     * * @return void
     */
    public function install(): void
    {
        self::newLine();
        self::info('Installing Slenix Veil authentication scaffolding...');
        self::newLine();

        $this->publishController();
        $this->publishMiddlewares();
        $this->publishViews();
        $this->publishMigration();
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
     * Publishes the main Authentication Controller.
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
     * Publishes the authentication and guest middlewares.
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
     * Publishes all authentication-related Luna views.
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
     * Generates and publishes the users table migration with a fresh timestamp.
     * * @return void
     */
    private function publishMigration(): void
    {
        $timestamp   = date('Y_m_d_His');
        $destination = $this->projectRoot . '/database/migrations/' . $timestamp . '_create_users_table.php';

        $this->publish(
            $this->stubsPath . '/migrations/create_users_table.stub',
            $destination,
            'create_users_table migration'
        );
    }

    /**
     * Appends the Veil route definitions to the web routes file.
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
     * Copies a stub file to its destination and creates directories if needed.
     *
     * @param string $stub The source stub path.
     * @param string $destination The destination path in the project.
     * @param string $label A descriptive label for console output.
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
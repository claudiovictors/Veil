<?php

/*
|--------------------------------------------------------------------------
| VeilInstallCommand — slenix/veil
|--------------------------------------------------------------------------
|
| Installs Veil authentication scaffolding into a Slenix project.
|
| Usage:
|   php celestial veil:install
|   php celestial veil:install --force   (overwrite existing files)
|
*/

declare(strict_types=1);

namespace Slenix\Veil\Console;

use Slenix\Core\Console\Command;
use Slenix\Veil\VeilServiceProvider;

class VeilInstallCommand extends Command
{
    private array $args;
    private bool  $force;
    private string $projectRoot;
    private string $stubsPath;

    public function __construct(array $args)
    {
        $this->args        = $args;
        $this->force       = in_array('--force', $args, true);
        $this->projectRoot = dirname(__DIR__, 5); // vai até a raiz do projeto
        $this->stubsPath   = VeilServiceProvider::stubsPath();
    }

    /**
     * Run the installer.
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

    // =========================================================================
    // Publishers
    // =========================================================================

    private function publishController(): void
    {
        $destination = $this->projectRoot . '/app/Controllers/AuthController.php';
        $stub        = $this->stubsPath . '/controllers/AuthController.stub';

        $this->publish($stub, $destination, 'AuthController');
    }

    private function publishMiddlewares(): void
    {
        $middlewares = [
            'AuthMiddleware'  => 'middlewares/AuthMiddleware.stub',
            'GuestMiddleware' => 'middlewares/GuestMiddleware.stub',
        ];

        foreach ($middlewares as $name => $stubFile) {
            $destination = $this->projectRoot . '/app/Middlewares/' . $name . '.php';
            $stub        = $this->stubsPath . '/' . $stubFile;
            $this->publish($stub, $destination, $name);
        }
    }

    private function publishViews(): void
    {
        $views = [
            'auth/layout.luna.php'    => 'views/layout.stub',
            'auth/login.luna.php'     => 'views/login.stub',
            'auth/register.luna.php'  => 'views/register.stub',
            'auth/dashboard.luna.php' => 'views/dashboard.stub',
        ];

        foreach ($views as $relative => $stubFile) {
            $destination = $this->projectRoot . '/views/' . $relative;
            $stub        = $this->stubsPath . '/' . $stubFile;
            $this->publish($stub, $destination, $relative);
        }
    }

    private function publishMigration(): void
    {
        $timestamp   = date('Y_m_d_His');
        $destination = $this->projectRoot . '/database/migrations/' . $timestamp . '_create_users_table.php';
        $stub        = $this->stubsPath . '/migrations/create_users_table.stub';

        $this->publish($stub, $destination, 'create_users_table migration');
    }

    private function appendRoutes(): void
    {
        $routesFile = $this->projectRoot . '/routes/web.php';
        $stub       = $this->stubsPath . '/routes.stub';

        if (!file_exists($routesFile)) {
            self::warning("routes/web.php not found. Skipping route injection.");
            return;
        }

        if (!file_exists($stub)) {
            self::warning("Routes stub not found. Skipping.");
            return;
        }

        $routesContent  = file_get_contents($routesFile);
        $stubContent    = file_get_contents($stub);
        $marker         = '// @veil-routes';

        // Não duplica se já foi instalado antes
        if (str_contains($routesContent, $marker)) {
            self::warning("Veil routes already present in web.php. Skipping.");
            return;
        }

        file_put_contents($routesFile, $routesContent . PHP_EOL . $stubContent);
        self::success("Routes appended → routes/web.php");
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    /**
     * Copies a stub to a destination, creating directories as needed.
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

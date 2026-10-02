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
use Slenix\Core\Console\Prompt;
use Slenix\Veil\VeilServiceProvider;

/**
 * VeilInstallCommand
 *
 * Installs Luna or React authentication scaffolding.
 *
 * Usage:
 *   php celestial veil:install
 *   php celestial veil:install --stack=luna
 *   php celestial veil:install --stack=react --force
 *   php celestial veil:install --no-interaction
 *
 * @version 2.0.0
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
    private bool $noInteraction;

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
    private string $stack;

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
        $this->noInteraction = in_array('--no-interaction', $args, true);
        $this->projectRoot = dirname(__DIR__, 5);
        $this->stubsPath = VeilServiceProvider::stubsPath();
        $this->stack = $this->resolveStack();
    }

    private static function findRoot(string $from): string
    {
        $dir = $from;
        while ($dir !== dirname($dir)) {
            if (is_file($dir . '/celestial')) {
                return $dir;
            }
            $dir = dirname($dir);
        }
        return getcwd() ?: $from;
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
        self::info('Stack: ' . strtoupper($this->stack));
        self::newLine();

        if ($this->stack === 'react' && !$this->ensureReactFrontend()) {
            self::warning('Falling back to Luna stack.');
            $this->stack = 'luna';
        }

        $this->detectExistingVariant();

        $this->publishModelIfMissing();
        $this->publishShared();
        $this->publishStack();
        $this->appendRoutes();

        self::newLine();
        self::success('Veil (' . $this->stack . ') installed successfully!');
        self::newLine();
        $this->printNextSteps();
    }

    private function resolveStack(): string
    {
        foreach ($this->args as $arg) {
            if (str_starts_with($arg, '--stack=')) {
                $value = strtolower(substr($arg, 8));
                if (in_array($value, ['luna', 'react'], true)) {
                    return $value;
                }
                self::error("Invalid --stack value: {$value}. Use luna or react.");
                exit(1);
            }
        }

        if ($this->noInteraction || !stream_isatty(STDIN)) {
            return 'luna';
        }

        $options = [
            'Luna (server-rendered HTML/CSS)',
            'React (multi-page, session auth)',
        ];
        $prompt = new Prompt();
        $label  = $prompt->select('Which authentication stack do you want to install?', $options);
        unset($prompt);

        return str_starts_with((string) $label, 'React') ? 'react' : 'luna';
    }

    private function ensureReactFrontend(): bool
    {
        $appJsx = $this->projectRoot . '/resources/js/app.jsx';
        $vite   = $this->projectRoot . '/vite.config.js';

        if (file_exists($appJsx) && file_exists($vite)) {
            return true;
        }

        self::warning('React frontend not detected (resources/js/app.jsx or vite.config.js missing).');

        if ($this->noInteraction || !stream_isatty(STDIN)) {
            return false;
        }

        $prompt = new Prompt();
        $run = $prompt->confirm('Run `php celestial frontend:install react` now?', false);
        unset($prompt);

        if (!$run) {
            return false;
        }

        passthru('php celestial frontend:install react', $code);

        return $code === 0 && file_exists($appJsx) && file_exists($vite);
    }

    private function detectExistingVariant(): void
    {
        $markerFile = $this->projectRoot . '/.veil-stack';
        if (!file_exists($markerFile)) {
            return;
        }

        $existing = trim((string) file_get_contents($markerFile));
        if ($existing !== '' && $existing !== $this->stack) {
            self::warning("A different Veil stack is already installed ({$existing}). Installing {$this->stack} may mix files.");
            if (!$this->force && !$this->noInteraction && stream_isatty(STDIN)) {
                $prompt = new Prompt();
                $ok = $prompt->confirm('Continue anyway?', false);
                unset($prompt);
                if (!$ok) {
                    self::info('Aborted.');
                    exit(0);
                }
            }
        }
    }

    private function publishModelIfMissing(): void
    {
        $dest = $this->projectRoot . '/app/Models/User.php';
        if (file_exists($dest)) {
            self::info('User model already exists — skipping.');
            return;
        }

        $this->publish(
            $this->stubsPath . '/shared/model/User.stub',
            $dest,
            'User model'
        );
    }

    private function publishShared(): void
    {
        $map = [
            'shared/middlewares/AuthMiddleware.stub'   => 'app/Middlewares/AuthMiddleware.php',
            'shared/middlewares/GuestMiddleware.stub'  => 'app/Middlewares/GuestMiddleware.php',
            'shared/Http/Requests/LoginRequest.stub'   => 'app/Http/Requests/LoginRequest.php',
            'shared/Http/Requests/RegisterRequest.stub'=> 'app/Http/Requests/RegisterRequest.php',
        ];

        foreach ($map as $stub => $dest) {
            $this->publish(
                $this->stubsPath . '/' . $stub,
                $this->projectRoot . '/' . $dest,
                $dest
            );
        }

        $logoStub = $this->stubsPath . '/shared/public/logo.png';
        if (file_exists($logoStub)) {
            $this->publish($logoStub, $this->projectRoot . '/public/logo.png', 'public/logo.png');
        }
    }

    private function publishStack(): void
    {
        if ($this->stack === 'luna') {
            $this->publishLuna();
        } else {
            $this->publishReact();
        }

        file_put_contents($this->projectRoot . '/.veil-stack', $this->stack);
    }

    private function publishLuna(): void
    {
        $controllers = [
            'luna/controllers/AuthController.stub'      => 'app/Controllers/AuthController.php',
            'luna/controllers/DashboardController.stub' => 'app/Controllers/DashboardController.php',
        ];

        foreach ($controllers as $stub => $dest) {
            $this->publish($this->stubsPath . '/' . $stub, $this->projectRoot . '/' . $dest, $dest);
        }

        $views = [
            'luna/views/app.stub'      => 'views/layouts/app.luna.php',
            'luna/views/guest.stub'    => 'views/layouts/guest.luna.php',
            'luna/views/login.stub'    => 'views/auth/login.luna.php',
            'luna/views/register.stub' => 'views/auth/register.luna.php',
            'luna/views/index.stub'    => 'views/dashboard/index.luna.php',
        ];

        foreach ($views as $stub => $dest) {
            $this->publish($this->stubsPath . '/' . $stub, $this->projectRoot . '/' . $dest, $dest);
        }

        $css = [
            'luna/css/style.css' => 'public/css/style.css',
            'luna/css/auth.css'  => 'public/css/auth.css',
        ];

        foreach ($css as $stub => $dest) {
            $this->publish($this->stubsPath . '/' . $stub, $this->projectRoot . '/' . $dest, $dest);
        }
    }

    private function publishReact(): void
    {
        $controllers = [
            'react/controllers/AuthController.stub'      => 'app/Controllers/AuthController.php',
            'react/controllers/DashboardController.stub' => 'app/Controllers/DashboardController.php',
        ];

        foreach ($controllers as $stub => $dest) {
            $this->publish($this->stubsPath . '/' . $stub, $this->projectRoot . '/' . $dest, $dest);
        }

        $pageStub = $this->stubsPath . '/react/views/page.stub';
        foreach ([
            'views/auth/login.luna.php',
            'views/auth/register.luna.php',
            'views/dashboard/index.luna.php',
        ] as $dest) {
            $this->publish($pageStub, $this->projectRoot . '/' . $dest, $dest);
        }

        $this->publish(
            $this->stubsPath . '/react/views/app.stub',
            $this->projectRoot . '/views/layouts/app.luna.php',
            'views/layouts/app.luna.php'
        );

        // Always backup existing app.jsx (including --force)
        $appJsx = $this->projectRoot . '/resources/js/app.jsx';
        if (file_exists($appJsx)) {
            $backup = $this->projectRoot . '/resources/js/app.veil-backup.jsx';
            copy($appJsx, $backup);
            self::info('Backed up existing app.jsx → resources/js/app.veil-backup.jsx');
        }

        foreach ([
            'react/resources/js/app.jsx' => 'resources/js/app.jsx',
            'react/resources/js/http.js' => 'resources/js/http.js',
        ] as $stub => $dest) {
            $this->publish($this->stubsPath . '/' . $stub, $this->projectRoot . '/' . $dest, $dest);
        }

        $componentsDir = $this->stubsPath . '/react/resources/js/components';
        if (is_dir($componentsDir)) {
            $targetDir = $this->projectRoot . '/resources/js/components';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            foreach (glob($componentsDir . '/*.jsx') ?: [] as $file) {
                $name = basename($file);
                $this->publish($file, $targetDir . '/' . $name, 'resources/js/components/' . $name);
            }
        }

        $this->publish(
            $this->stubsPath . '/react/resources/css/veil.css',
            $this->projectRoot . '/resources/css/veil.css',
            'resources/css/veil.css'
        );
    }

    private function appendRoutes(): void
    {
        $routesFile = $this->projectRoot . '/routes/web.php';
        $stub = $this->stubsPath . '/shared/routes.stub';
        $start = '// @veil-routes';
        $end   = '// @end-veil-routes';

        if (!file_exists($routesFile) || !file_exists($stub)) {
            self::warning('routes/web.php or routes stub missing. Skipping route injection.');
            return;
        }

        $content = file_get_contents($routesFile);
        $block   = trim(file_get_contents($stub));

        if (str_contains($content, $start) && str_contains($content, $end)) {
            $pattern = '/' . preg_quote($start, '/') . '.*?' . preg_quote($end, '/') . '/s';
            $content = preg_replace_callback($pattern, static fn () => $block, $content);
            file_put_contents($routesFile, $content);
            self::success('Routes block replaced → routes/web.php');
            return;
        }

        file_put_contents($routesFile, rtrim($content) . PHP_EOL . PHP_EOL . $block . PHP_EOL);
        self::success('Routes appended → routes/web.php');
    }

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

    private function printNextSteps(): void
    {
        self::info('Next steps:');
        if ($this->stack === 'react') {
            echo '  1. npm install' . PHP_EOL;
            echo '  2. npm run dev' . PHP_EOL;
            echo '  3. php celestial migrate' . PHP_EOL;
            echo '  4. php celestial serve' . PHP_EOL;
        } else {
            echo '  1. php celestial migrate' . PHP_EOL;
            echo '  2. php celestial serve' . PHP_EOL;
            echo '  3. Visit /login or /register' . PHP_EOL;
        }
        self::newLine();
    }
}
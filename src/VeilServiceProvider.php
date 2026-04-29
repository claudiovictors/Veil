<?php

/*
|--------------------------------------------------------------------------
| Veil Service Provider
|--------------------------------------------------------------------------
|
| This class serves as the central point for the Veil package, providing
| essential paths and configuration logic. It primarily manages the 
| location of boilerplate stubs used during the installation process.
|
*/

declare(strict_types=1);

namespace Slenix\Veil;

class VeilServiceProvider
{
    /**
     * Get the absolute path to the package stubs directory.
     *
     * This path contains the template files (.stub) used by the install 
     * command to scaffold the authentication system.
     *
     * @return string The directory path to the stubs.
     */
    public static function stubsPath(): string
    {
        return dirname(__DIR__) . '/src/Stubs';
    }
}
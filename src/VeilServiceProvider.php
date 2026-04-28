<?php

declare(strict_types=1);

namespace Slenix\Veil;

class VeilServiceProvider
{
    /**
     * Returns the absolute path to the stubs directory.
     */
    public static function stubsPath(): string
    {
        return dirname(__DIR__) . '/src/Stubs';
    }
}
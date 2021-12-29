<?php
declare(strict_types=1);

namespace Fyre\Loader;

use
    Fyre\Utility\Path;

use const
    DIRECTORY_SEPARATOR;

use function
    array_key_exists,
    array_merge,
    is_file,
    in_array,
    rtrim,
    spl_autoload_register,
    str_replace,
    str_starts_with,
    strlen,
    substr;

/**
 * Loader
 */
abstract class Loader
{

    protected static array $namespaces = [];

    protected static array $classMap = [];

    protected static bool $registered = false;

    /**
     * Add a class map.
     * @param array $classMap The class map.
     */
    public static function addClassMap(array $classMap): void
    {
        static::$classMap = array_merge(static::$classMap, $classMap);
    }

    /**
     * Add namespaces.
     * @param array $namespaces The namespaces.
     */
    public static function addNamespaces(array $namespaces): void
    {
        foreach ($namespaces AS $prefix => $paths) {
            $prefix = static::formatPrefix($prefix);
    
            static::$namespaces[$prefix] ??= [];
    
            $paths = (array) $paths;

            foreach ($paths AS $path) {
                $path = Path::resolve($path);

                if (in_array($path, static::$namespaces[$prefix])) {
                    continue;
                }

                static::$namespaces[$prefix][] = $path;
            }
        }
    }

    /**
     * Clear the auto loader.
     */
    public static function clear()
    {
        static::$namespaces = [];
        static::$classMap = [];
    }

    /**
     * Get a namespace.
     * @param string $prefix The namespace prefix.
     * @return array The namespace paths.
     */
    public static function getNamespace(string $prefix): array
    {
        $prefix = static::formatPrefix($prefix);

        return static::$namespaces[$prefix] ?? [];
    }

    /**
     * Load composer.
     * @param string $composerPath The composer autload path.
     */
    public static function loadComposer(string $composerPath): void
    {
        if (!is_file($composerPath)) {
            return;
        }

        $composer = include_once $composerPath;

        $classMap = $composer->getClassMap();
        $namespaces = $composer->getPrefixesPsr4();

        static::addClassMap($classMap);
        static::addNamespaces($namespaces);
    }

    /**
     * Register the autoloader.
     */
    public static function register(): void
    {
        if (static::$registered) {
            return;
        }

        spl_autoload_register([static::class, 'loadClass'], true, true);
        spl_autoload_register([static::class, 'loadClassFromMap'], true, true);

        static::$registered = true;
    }

    /**
     * Remove a namespace.
     * @param string $prefix The namespace prefix.
     */
    public static function removeNamespace(string $prefix): void
    {
        $prefix = static::formatPrefix($prefix);

        unset(static::$namespaces[$prefix]);
    }

    /**
     * Unregister the autoloader.
     */
    public static function unregister(): void
    {
        if (!static::$registered) {
            return;
        }

        spl_autoload_unregister([static::class, 'loadClass']);
        spl_autoload_unregister([static::class, 'loadClassFromMap']);

        static::$registered = false;
    }

    /**
     * Format a namespace prefix.
     * @param string $prefix The namespace prefix.
     * @return string The formatted namespace prefix.
     */
    protected static function formatPrefix(string $prefix): string
    {
        return rtrim($prefix, '\\').'\\';
    }

    /**
     * Attempt to load a class.
     * @param string $class The class name.
     * @return string|bool The file name, or FALSE if the class could not be loaded.
     */
    protected static function loadClass(string $class): string|bool
    {
        foreach (static::$namespaces AS $namespace => $paths) {
            if (!str_starts_with($class, $namespace)) {
                continue;
            }

            $length = strlen($namespace);
            $fileName = substr($class, $length);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $fileName);
            $fileName .= '.php';

            foreach ($paths AS $path) {
                $filePath = Path::join($path, $fileName);

                if (static::loadFile($filePath)) {
                    return $filePath;
                }
            }
        }

        return false;
    }

    /**
     * Attempt to load a class from the class map.
     * @param string $class The class name.
     * @return string|bool The file name, or FALSE if the class could not be loaded.
     */
    protected static function loadClassFromMap(string $class): string|bool
    {
        if (!array_key_exists($class, static::$classMap)) {
            return false;
        }

        return static::loadFile(static::$classMap[$class]);
    }

    /**
     * Attempt to load a file.
     * @param string $file The file path.
     * @return string|bool The file path, or FALSE if the file co uld not be loaded.
     */
    protected static function loadFile(string $file): string|bool
    {
        if (!is_file($file)) {
            return false;
        }

        include_once $file;

        return $file;
    }

}

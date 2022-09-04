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
    spl_autoload_register,
    str_replace,
    str_starts_with,
    strlen,
    substr,
    trim;

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
        foreach ($classMap AS $class => $path) {
            $class = ltrim($class, '\\');
            $path = Path::resolve($path);

            static::$classMap[$class] = $path;
        }
    }

    /**
     * Add namespaces.
     * @param array $namespaces The namespaces.
     */
    public static function addNamespaces(array $namespaces): void
    {
        foreach ($namespaces AS $prefix => $paths) {
            $prefix = static::normalizeNamespace($prefix);
    
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
        $prefix = static::normalizeNamespace($prefix);

        return static::$namespaces[$prefix] ?? [];
    }

    /**
     * Get all paths for a namespace.
     * @param string $prefix The namespace prefix.
     * @return array The namespace paths.
     */
    public static function getNamespacePaths(string $prefix): array
    {
        $prefix = static::normalizeNamespace($prefix);
        $prefixLength = strlen($prefix);

        $paths = static::$namespaces[$prefix] ?? [];

        foreach (static::$classMap AS $className => $filePath) {
            if (!str_starts_with($className, $prefix)) {
                continue;
            }

            $classSuffix = substr($className, $prefixLength - 1);

            $testPath = str_replace('\\', DIRECTORY_SEPARATOR, $classSuffix);
            $testPath .= '.php';

            if (!str_ends_with($filePath, $testPath)) {
                continue;
            }

            $testPathLength = strlen($testPath);
            $path = substr($filePath, 0, -$testPathLength);

            if (in_array($path, $paths)) {
                continue;
            }

            $paths[] = $path;
        }

        return $paths;
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

        static::$registered = true;
    }

    /**
     * Remove a namespace.
     * @param string $prefix The namespace prefix.
     */
    public static function removeNamespace(string $prefix): void
    {
        $prefix = static::normalizeNamespace($prefix);

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

        static::$registered = false;
    }

    /**
     * Attempt to load a class.
     * @param string $class The class name.
     * @return string|bool The file name, or FALSE if the class could not be loaded.
     */
    protected static function loadClass(string $class): string|bool
    {
        if (static::loadClassFromMap($class)) {
            return true;
        }

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

    /**
     * Normalize a namespace
     * @param string $namespace The namespace.
     * @return string The normalized namespace.
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        return trim($namespace, '\\').'\\';
    }

}

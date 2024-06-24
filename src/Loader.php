<?php
declare(strict_types=1);

namespace Fyre\Loader;

use Fyre\Utility\Path;

use function array_key_exists;
use function in_array;
use function is_file;
use function spl_autoload_register;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

use const DIRECTORY_SEPARATOR;

/**
 * Loader
 */
abstract class Loader
{
    protected static array $classMap = [];
    protected static array $namespaces = [];
    protected static bool $registered = false;

    /**
     * Add a class map.
     * @param array $classMap The class map.
     */
    public static function addClassMap(array $classMap): void
    {
        foreach ($classMap as $className => $path) {
            $className = static::normalizeClass($className);
            $path = Path::resolve($path);

            static::$classMap[$className] = $path;
        }
    }

    /**
     * Add namespaces.
     * @param array $namespaces The namespaces.
     */
    public static function addNamespaces(array $namespaces): void
    {
        foreach ($namespaces as $prefix => $paths) {
            $prefix = static::normalizeNamespace($prefix);

            static::$namespaces[$prefix] ??= [];

            $paths = (array) $paths;

            foreach ($paths as $path) {
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
    public static function clear(): void
    {
        static::$namespaces = [];
        static::$classMap = [];
    }

    /**
     * Get the class map.
     * @return array The class map.
     */
    public static function getClassMap(): array
    {
        return static::$classMap;
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

        foreach (static::$classMap as $className => $filePath) {
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
     * Get the namespaces.
     * @return array The namespaces.
     */
    public static function getNamespaces(): array
    {
        return static::$namespaces;
    }

    /**
     * Determine if a namespace exists.
     * @param string $prefix The namespace prefix.
     * @return bool TRUE if the namespace exists, otherwise FALSE.
     */
    public static function hasNamespace(string $prefix): bool
    {
        $prefix = static::normalizeNamespace($prefix);

        return array_key_exists($prefix, static::$namespaces);
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
     * Remove a class name.
     * @param string $className The class name.
     * @return bool TRUE if the class was removed, otherwise FALSE.
     */
    public static function removeClass(string $className): bool
    {
        $className = static::normalizeClass($className);

        if (!array_key_exists($className, static::$classMap)) {
            return false;
        }

        unset(static::$classMap[$className]);

        return true;
    }

    /**
     * Remove a namespace.
     * @param string $prefix The namespace prefix.
     * @return bool TRUE If the namespace was removed, otherwise FALSE.
     */
    public static function removeNamespace(string $prefix): bool
    {
        $prefix = static::normalizeNamespace($prefix);

        if (!array_key_exists($prefix, static::$namespaces)) {
            return false;
        }

        unset(static::$namespaces[$prefix]);

        return true;
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
    protected static function loadClass(string $class): bool|string
    {
        if (static::loadClassFromMap($class)) {
            return true;
        }

        foreach (static::$namespaces as $prefix => $paths) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $length = strlen($prefix);
            $fileName = substr($class, $length);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $fileName);
            $fileName .= '.php';

            foreach ($paths as $path) {
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
    protected static function loadClassFromMap(string $class): bool|string
    {
        if (!array_key_exists($class, static::$classMap)) {
            return false;
        }

        return static::loadFile(static::$classMap[$class]);
    }

    /**
     * Attempt to load a file.
     * @param string $filePath The file path.
     * @return string|bool The file path, or FALSE if the file co uld not be loaded.
     */
    protected static function loadFile(string $filePath): bool|string
    {
        if (!is_file($filePath)) {
            return false;
        }

        include_once $filePath;

        return $filePath;
    }

    /**
     * Normalize a class name
     * @param string $className The class name.
     * @return string The normalized class name.
     */
    protected static function normalizeClass(string $className): string
    {
        return ltrim($className, '\\');
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

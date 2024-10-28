<?php
declare(strict_types=1);

namespace Fyre\Loader;

use Closure;
use Fyre\Utility\Path;

use function array_key_exists;
use function in_array;
use function is_file;
use function rtrim;
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
class Loader
{
    protected array $classMap = [];

    protected Closure|null $loader = null;

    protected array $namespaces = [];

    /**
     * Add a class map.
     *
     * @param array $classMap The class map.
     */
    public function addClassMap(array $classMap): void
    {
        foreach ($classMap as $className => $path) {
            $className = static::normalizeClass($className);
            $path = Path::resolve($path);

            $this->classMap[$className] = $path;
        }
    }

    /**
     * Add namespaces.
     *
     * @param array $namespaces The namespaces.
     */
    public function addNamespaces(array $namespaces): void
    {
        foreach ($namespaces as $prefix => $paths) {
            $prefix = static::normalizeNamespace($prefix);

            $this->namespaces[$prefix] ??= [];

            $paths = (array) $paths;

            foreach ($paths as $path) {
                $path = Path::resolve($path);

                if ($path !== DIRECTORY_SEPARATOR) {
                    $path = rtrim($path, DIRECTORY_SEPARATOR);
                }

                if (in_array($path, $this->namespaces[$prefix])) {
                    continue;
                }

                $this->namespaces[$prefix][] = $path;
            }
        }
    }

    /**
     * Clear the auto loader.
     */
    public function clear(): void
    {
        $this->namespaces = [];
        $this->classMap = [];
    }

    /**
     * Get the class map.
     *
     * @return array The class map.
     */
    public function getClassMap(): array
    {
        return $this->classMap;
    }

    /**
     * Get a namespace.
     *
     * @param string $prefix The namespace prefix.
     * @return array The namespace paths.
     */
    public function getNamespace(string $prefix): array
    {
        $prefix = static::normalizeNamespace($prefix);

        return $this->namespaces[$prefix] ?? [];
    }

    /**
     * Get all paths for a namespace.
     *
     * @param string $prefix The namespace prefix.
     * @return array The namespace paths.
     */
    public function getNamespacePaths(string $prefix): array
    {
        $prefix = static::normalizeNamespace($prefix);
        $prefixLength = strlen($prefix);

        $paths = $this->namespaces[$prefix] ?? [];

        foreach ($this->classMap as $className => $filePath) {
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
            $path = substr($filePath, 0, -$testPathLength) ?: DIRECTORY_SEPARATOR;

            if (in_array($path, $paths)) {
                continue;
            }

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Get the namespaces.
     *
     * @return array The namespaces.
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Determine if a namespace exists.
     *
     * @param string $prefix The namespace prefix.
     * @return bool TRUE if the namespace exists, otherwise FALSE.
     */
    public function hasNamespace(string $prefix): bool
    {
        $prefix = static::normalizeNamespace($prefix);

        return array_key_exists($prefix, $this->namespaces);
    }

    /**
     * Load composer.
     *
     * @param string $composerPath The composer autload path.
     */
    public function loadComposer(string $composerPath): void
    {
        if (!is_file($composerPath)) {
            return;
        }

        $composer = include_once $composerPath;

        $classMap = $composer->getClassMap();
        $namespaces = $composer->getPrefixesPsr4();

        $this->addClassMap($classMap);
        $this->addNamespaces($namespaces);
    }

    /**
     * Register the autoloader.
     */
    public function register(): void
    {
        if ($this->loader) {
            return;
        }

        $this->loader = fn(string $class): bool|string => $this->loadClass($class);

        spl_autoload_register($this->loader, true, true);
    }

    /**
     * Remove a class name.
     *
     * @param string $className The class name.
     * @return bool TRUE if the class was removed, otherwise FALSE.
     */
    public function removeClass(string $className): bool
    {
        $className = static::normalizeClass($className);

        if (!array_key_exists($className, $this->classMap)) {
            return false;
        }

        unset($this->classMap[$className]);

        return true;
    }

    /**
     * Remove a namespace.
     *
     * @param string $prefix The namespace prefix.
     * @return bool TRUE If the namespace was removed, otherwise FALSE.
     */
    public function removeNamespace(string $prefix): bool
    {
        $prefix = static::normalizeNamespace($prefix);

        if (!array_key_exists($prefix, $this->namespaces)) {
            return false;
        }

        unset($this->namespaces[$prefix]);

        return true;
    }

    /**
     * Unregister the autoloader.
     */
    public function unregister(): void
    {
        if (!$this->loader) {
            return;
        }

        spl_autoload_unregister($this->loader);

        $this->loader = null;
    }

    /**
     * Attempt to load a class.
     *
     * @param string $class The class name.
     * @return bool|string The file name, or FALSE if the class could not be loaded.
     */
    protected function loadClass(string $class): bool|string
    {
        if ($this->loadClassFromMap($class)) {
            return true;
        }

        foreach ($this->namespaces as $prefix => $paths) {
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
     *
     * @param string $class The class name.
     * @return bool|string The file name, or FALSE if the class could not be loaded.
     */
    protected function loadClassFromMap(string $class): bool|string
    {
        if (!array_key_exists($class, $this->classMap)) {
            return false;
        }

        return static::loadFile($this->classMap[$class]);
    }

    /**
     * Attempt to load a file.
     *
     * @param string $filePath The file path.
     * @return bool|string The file path, or FALSE if the file co uld not be loaded.
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
     *
     * @param string $className The class name.
     * @return string The normalized class name.
     */
    protected static function normalizeClass(string $className): string
    {
        return ltrim($className, '\\');
    }

    /**
     * Normalize a namespace
     *
     * @param string $namespace The namespace.
     * @return string The normalized namespace.
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        return trim($namespace, '\\').'\\';
    }
}

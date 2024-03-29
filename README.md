# FyreLoader

**FyreLoader** is a free, open-source autoloader library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Methods](#methods)



## Installation

**Using Composer**

```
composer require fyre/loader
```

In PHP:

```php
use Fyre\Loader\Loader;
```


## Methods

**Add Class Map**

Add a class map.

- `$classMap` is an array containing the class map.

```php
Loader::addClassMap($classMap);
```

**Add Namespaces**

Add namespaces.

- `$namespaces` is an array containing the namespaces where the key is the namespace prefix and the value is the path.

```php
Loader::addNamespaces($namespaces);
```

**Clear**

Clear the auto loader.

```php
Loader::clear();
```

**Get Class Map**

Get the class map.

```php
$classMap = Loader::getClassMap();
```

**Get Namespace**

Get a namespace.

- `$prefix` is a string representing the namespace prefix.

```php
$paths = Loader::getNamespace($prefix);
```

**Get Namespace Paths**

Get all paths for a namespace.

- `$prefix` is a string representing the namespace prefix.

```php
$paths = Loader::getNamespacePaths($prefix);
```

**Get Namespaces**

Get the namespaces.

```php
$namespaces = Loader::getNamespaces();
```

**Has Namespace**

Check if a namespace exists.

- `$prefix` is a string representing the namespace prefix.

```php
$hasNamespace = Loader::hasNamespace($prefix);
```

**Load Composer**

Load composer.

- `$composerPath` is a string representing the composer autoload path.

```php
Loader::loadComposer($composerPath);
```

**Register**

Register the autoloader.

```php
Loader::register();
```

**Remove Class**

Remove a class.

- `$className` is a string representing the class name.

```php
$removed = Loader::removeClass($className);
```

**Remove Namespace**

Remove a namespace.

- `$prefix` is a string representing the namespace prefix.

```php
$removed = Loader::removeNamespace($prefix);
```

**Unregister**

Unregister the autoloader.

```php
Loader::unregister();
```
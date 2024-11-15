# FyreLoader

**FyreLoader** is a free, open-source autoloader library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Basic Usage](#basic-usage)
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


## Basic Usage

```php
$loader = new Loader();
```

**Autoloading**

It is recommended to bind the *Loader* to the [*Container*](https://github.com/elusivecodes/FyreContainer) as a singleton.

```php
$container->singleton(Loader::class);
```


## Methods

**Add Class Map**

Add a class map.

- `$classMap` is an array containing the class map.

```php
$loader->addClassMap($classMap);
```

**Add Namespaces**

Add namespaces.

- `$namespaces` is an array containing the namespaces where the key is the namespace prefix and the value is the path.

```php
$loader->addNamespaces($namespaces);
```

**Clear**

Clear the auto loader.

```php
$loader->clear();
```

**Get Class Map**

Get the class map.

```php
$classMap = $loader->getClassMap();
```

**Get Namespace**

Get a namespace.

- `$prefix` is a string representing the namespace prefix.

```php
$paths = $loader->getNamespace($prefix);
```

**Get Namespace Paths**

Get all paths for a namespace.

- `$prefix` is a string representing the namespace prefix.

```php
$paths = $loader->getNamespacePaths($prefix);
```

**Get Namespaces**

Get the namespaces.

```php
$namespaces = $loader->getNamespaces();
```

**Has Namespace**

Determine whether a namespace exists.

- `$prefix` is a string representing the namespace prefix.

```php
$hasNamespace = $loader->hasNamespace($prefix);
```

**Load Composer**

Load composer.

- `$composerPath` is a string representing the composer autoload path.

```php
$loader->loadComposer($composerPath);
```

**Register**

Register the autoloader.

```php
$loader->register();
```

**Remove Class**

Remove a class.

- `$className` is a string representing the class name.

```php
$loader->removeClass($className);
```

**Remove Namespace**

Remove a namespace.

- `$prefix` is a string representing the namespace prefix.

```php
$loader->removeNamespace($prefix);
```

**Unregister**

Unregister the autoloader.

```php
$loader->unregister();
```
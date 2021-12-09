# FyreLoader

**FyreLoader** is a free, autoloader library for *PHP*.


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

- `$namespaces` is an array containing the namespaces where the key is the namespace and the value is the path.

```php
Loader::addNamespaces($namespaces);
```

**Clear**

Clear the auto loader.

```php
Loader::clear();
```

**Get Namespace**

Get a namespace.

- `$namespace` is a string representing the namespace prefix.

```php
$paths = Loader::getNamespace($namespace);
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

**Remove Namespace**

Remove a namespace.

- `$namespace` is a string representing the namespace prefix.

```php
Loader::removeNamespace($namespace);
```

**Unregister**

Unregister the autoloader.

```php
Loader::unregister();
```
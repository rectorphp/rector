Nette Caching
=============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/caching.svg)](https://packagist.org/packages/nette/caching)
[![Tests](https://github.com/nette/caching/workflows/Tests/badge.svg?branch=master)](https://github.com/nette/caching/actions)
[![Coverage Status](https://coveralls.io/repos/github/nette/caching/badge.svg?branch=master)](https://coveralls.io/github/nette/caching?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/caching/v/stable)](https://github.com/nette/caching/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/caching/blob/master/license.md)


Introduction
============

Cache accelerates your application by storing data - once hardly retrieved - for future use.

Documentation can be found on the [website](https://doc.nette.org/caching).


[Support Me](https://github.com/sponsors/dg)
--------------------------------------------

Do you like Nette Caching? Are you looking forward to the new features?

[![Buy me a coffee](https://files.nette.org/icons/donation-3.svg)](https://github.com/sponsors/dg)

Thank you!


Installation
------------

```
composer require nette/caching
```

It requires PHP version 7.2 and supports PHP up to 8.0.


Basic Usage
===========

The center of work with the cache is the object [Nette\Caching\Cache](https://api.nette.org/3.0/Nette/Caching/Cache.html). We create its instance and pass the so-called storage to the constructor as a parameter. Which is an object representing the place where the data will be physically stored (database, Memcached, files on disk, ...). You will find out all the essentials in [section Storages](#Storages).

For the following examples, suppose we have an alias `Cache` and a storage in the variable `$storage`.

```php
use Nette\Caching\Cache;

$storage // instance of Nette\Caching\IStorage
```

The cache is actually a *key–value store*, so we read and write data under keys just like associative arrays. Applications consist of a number of independent parts, and if they all used one storage (for idea: one directory on a disk), sooner or later there would be a key collision. The Nette Framework solves the problem by dividing the entire space into namespaces (subdirectories). Each part of the program then uses its own space with a unique name and no collisions can occur.

The name of the space is specified as the second parameter of the constructor of the Cache class:

```php
$cache = new Cache($storage, 'Full Html Pages');
```

We can now use object `$cache` to read and write from the cache. The method `load()` is used for both. The first argument is the key and the second is the PHP callback, which is called when the key is not found in the cache. The callback generates a value, returns it and caches it:

```php
$value = $cache->load($key, function () use ($key) {
	$computedValue = ...; // heavy computations
	return $computedValue;
});
```

If the second parameter is not specified `$value = $cache->load($key)`, the `null` is returned if the item is not in the cache.

The great thing is that any serializable structures can be cached, not only strings. And the same applies for keys.

The item is cleared from the cache using method `remove()`:

```php
$cache->remove($key);
```

You can also cache an item using method `$cache->save($key, $value, array $dependencies = [])`. However, the above method using `load()` is preferred.




Memoization
===========

Memoization means caching the result of a function or method so you can use it next time instead of calculating the same thing again and again.

Methods and functions can be called memoized using `call(callable $callback, ...$args)`:

```php
$result = $cache->call('gethostbyaddr', $ip);
```

The function `gethostbyaddr()` is called only once for each parameter `$ip` and the next time the value from the cache will be returned.

It is also possible to create a memoized wrapper for a method or function that can be called later:

```php
function factorial($num)
{
	return ...;
}

$memoizedFactorial = $cache->wrap('factorial');

$result = $memoizedFactorial(5); // counts it
$result = $memoizedFactorial(5); // returns it from cache
```


Expiration & Invalidation
=========================

With caching, it is necessary to address the question that some of the previously saved data will become invalid over time. Nette Framework provides a mechanism, how to limit the validity of data and how to delete them in a controlled way ("to invalidate them", using the framework's terminology).

The validity of the data is set at the time of saving using the third parameter of the method `save()`, eg:

```php
$cache->save($key, $value, [
	Cache::EXPIRE => '20 minutes',
]);
```

Or using the `$dependencies` parameter passed by reference to the callback in the `load()` method, eg:

```php
$value = $cache->load($key, function (&$dependencies) {
	$dependencies[Cache::EXPIRE] = '20 minutes';
	return ...;
]);
```

In the following examples, we will assume the second variant and thus the existence of a variable `$dependencies`.


Expiration
----------

The simplest exiration is the time limit. Here's how to cache data valid for 20 minutes:

```php
// it also accepts the number of seconds or the UNIX timestamp
$dependencies[Cache::EXPIRE] = '20 minutes';
```

If we want to extend the validity period with each reading, it can be achieved this way, but beware, this will increase the cache overhead:

```php
$dependencies[Cache::SLIDING] = true;
```

The handy option is the ability to let the data expire when a particular file is changed or one of several files. This can be used, for example, for caching data resulting from procession these files. Use absolute paths.

```php
$dependencies[Cache::FILES] = '/path/to/data.yaml';
// nebo
$dependencies[Cache::FILES] = ['/path/to/data1.yaml', '/path/to/data2.yaml'];
```

We can let an item in the cache expired when another item (or one of several others) expires. This can be used when we cache the entire HTML page and fragments of it under other keys. Once the snippet changes, the entire page becomes invalid. If we have fragments stored under keys such as `frag1` and `frag2`, we will use:

```php
$dependencies[Cache::ITEMS] = ['frag1', 'frag2'];
```

Expiration can also be controlled using custom functions or static methods, which always decide when reading whether the item is still valid. For example, we can let the item expire whenever the PHP version changes. We will create a function that compares the current version with the parameter, and when saving we will add an array in the form `[function name, ...arguments]` to the dependencies:

```php
function checkPhpVersion($ver): bool
{
	return $ver === PHP_VERSION_ID;
}

$dependencies[Cache::CALLBACKS] = [
	['checkPhpVersion', PHP_VERSION_ID] // expire when checkPhpVersion(...) === false
];
```

Of course, all criteria can be combined. The cache then expires when at least one criterion is not met.

```php
$dependencies[Cache::EXPIRE] = '20 minutes';
$dependencies[Cache::FILES] = '/path/to/data.yaml';
```



Invalidation using Tags
-----------------------

Tags are a very useful invalidation tool. We can assign a list of tags, which are arbitrary strings, to each item stored in the cache. For example, suppose we have an HTML page with an article and comments, which we want to cache. So we specify tags when saving to cache:

```php
$dependencies[Cache::TAGS] = ["article/$articleId", "comments/$articleId"];
```

Now, let's move to the administration. Here we have a form for article editing. Together with saving the article to a database, we call the `clean()` command, which will delete cached items by tag:

```php
$cache->clean([
	Cache::TAGS => ["article/$articleId"],
]);
```

Likewise, in the place of adding a new comment (or editing a comment), we will not forget to invalidate the relevant tag:

```php
$cache->clean([
	Cache::TAGS => ["comments/$articleId"],
]);
```

What have we achieved? That our HTML cache will be invalidated (deleted) whenever the article or comments change. When editing an article with ID = 10, the tag `article/10` is forced to be invalidated and the HTML page carrying the tag is deleted from the cache. The same happens when you insert a new comment under the relevant article.

Tags require [Journal](#Journal).


Invalidation by Priority
------------------------

We can set the priority for individual items in the cache, and it will be possible to delete them in a controlled way when, for example, the cache exceeds a certain size:

```php
$dependencies[Cache::PRIORITY] = 50;
```

Delete all items with a priority equal to or less than 100:

```php
$cache->clean([
	Cache::PRIORITY => 100,
]);
```

Priorities require so-called [Journal](#Journal).


Clear Cache
-----------

The `Cache::ALL` parameter clears everything:

```php
$cache->clean([
	Cache::ALL => true,
]);
```


Bulk Reading
============

For bulk reading and writing to cache, the `bulkLoad()` method is used, where we pass an array of keys and obtain an array of values:

```php
$values = $cache->bulkLoad($keys);
```

Method `bulkLoad()` works similarly to `load()` with the second callback parameter, to which the key of the generated item is passed:

```php
$values = $cache->bulkLoad($keys, function ($key, &$dependencies) {
	$computedValue = ...; // heavy computations
	return $computedValue;
});
```


Output Caching
==============

The output can be captured and cached very elegantly:

```php
if ($capture = $cache->start($key)) {

	echo ... // printing some data

	$capture->end(); // save the output to the cache
}
```

In case that the output is already present in the cache, the `start()` method prints it and returns `null`, so the condition will not be executed. Otherwise, it starts to buffer the output and returns the `$capture` object using which we finally save the data to the cache.


Caching in Latte
================

Caching in templates [Latte](https://latte.nette.org) is very easy, just wrap part of the template with tags `{cache}...{/cache}`. The cache is automatically invalidated when the source template changes (including any included templates within the `{cache}` tags). Tags `{cache}` can be nested, and when a nested block is invalidated (for example, by a tag), the parent block is also invalidated.

In the tag it is possible to specify the keys to which the cache will be bound (here the variable `$id`) and set the expiration and [invalidation tags](#invalidation-using-tags).

```html
{cache $id, expire => '20 minutes', tags => [tag1, tag2]}
	...
{/cache}
```

All parameters are optional, so you don't have to specify expiration, tags, or keys.

The use of the cache can also be conditioned by `if` - the content will then be cached only if the condition is met:

```html
{cache $id, if => !$form->isSubmitted()}
	{$form}
{/cache}
```


Storages
========

A storage is an object that represents where data is physically stored. We can use a database, a Memcached server, or the most available storage, which are files on disk.

Storage | 			Description
--------|----------------------
FileStorage | 		default storage with saving to files on disk
MemcachedStorage | 	uses the `Memcached` server
MemoryStorage | 	data are temporarily in memory
SQLiteStorage | 	data is stored in SQLite database
DevNullStorage | 	data aren't stored - for testing purposes


FileStorage
-----------

Writes the cache to files on disk. The storage `Nette\Caching\Storages\FileStorage` is very well optimized for performance and above all ensures full atomicity of operations. What does it mean? That when using the cache, it cannot happen that we read a file that has not yet been completely written by another thread, or that someone would delete it "under your hands". The use of the cache is therefore completely safe.

This storage also has an important built-in feature that prevents an extreme increase in CPU usage when the cache is cleared or cold (ie not created). This is [cache stampede](https://en.wikipedia.org/wiki/Cache_stampede) prevention.
It happens that at one moment there are several concurrent requests that want the same thing from the cache (eg the result of an expensive SQL query) and because it is not cached, all processes start executing the same SQL query.
The processor load is multiplied and it can even happen that no thread can respond within the time limit, the cache is not created and the application crashes.
Fortunately, the cache in Nette works in such a way that when there are multiple concurrent requests for one item, it is generated only by the first thread, the others wait and then use the generated result.

Example of creating a FileStorage:

```php
// the storage will be the directory '/path/to/temp' on the disk
$storage = new Nette\Caching\Storages\FileStorage('/path/to/temp');
```

MemcachedStorage
----------------

The server [Memcached](https://memcached.org) is a high-performance distributed storage system whose adapter is `Nette\Caching\Storages\MemcachedStorage`.

Requires PHP extension `memcached`.

```php
$storage = new Nette\Caching\Storages\MemcachedStorage('10.0.0.158');
```

MemoryStorage
-------------

`Nette\Caching\Storages\MemoryStorage` is a storage that stores data in a PHP array and is thus lost when the request is terminated.

```php
$storage = new Nette\Caching\Storages\MemoryStorage;
```


SQLiteStorage
-------------

The SQLite database and adapter `Nette\Caching\Storages\SQLiteStorage` offer a way to cache in a single file on disk. The configuration will specify the path to this file.

Requires PHP extensions `pdo` and `pdo_sqlite`.

```php
$storage = new Nette\Caching\Storages\SQLiteStorage('/path/to/cache.sdb');
```


DevNullStorage
--------------

A special implementation of storage is `Nette\Caching\Storages\DevNullStorage`, which does not actually store data at all. It is therefore suitable for testing if we want to eliminate the effect of the cache.

```php
$storage = new Nette\Caching\Storages\DevNullStorage;
```


Journal
=======

Nette stores tags and priorities in a so-called journal. By default, SQLite and file `journal.s3db` are used for this, and **PHP extensions `pdo` and `pdo_sqlite` are required.**

If you like Nette, **[please make a donation now](https://github.com/sponsors/dg)**. Thank you!

Nette Finder: Files Searching
=============================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/finder.svg)](https://packagist.org/packages/nette/finder)
[![Build Status](https://travis-ci.org/nette/finder.svg?branch=master)](https://travis-ci.org/nette/finder)
[![Coverage Status](https://coveralls.io/repos/github/nette/finder/badge.svg?branch=master)](https://coveralls.io/github/nette/finder?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/finder/v/stable)](https://github.com/nette/finder/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/finder/blob/master/license.md)


Introduction
------------

Nette Finder makes browsing the directory structure really easy.

Documentation can be found on the [website](https://doc.nette.org/finder).

If you like Nette, **[please make a donation now](https://nette.org/donate)**. Thank you!


Installation
------------

The recommended way to install is via Composer:

```
composer require nette/finder
```

It requires PHP version 7.1 and supports PHP up to 7.4.


Usage
-----

How to find all `*.txt` files in `$dir` directory without recursing subdirectories?

```php
foreach (Finder::findFiles('*.txt')->in($dir) as $key => $file) {
	echo $key; // $key is a string containing absolute filename with path
	echo $file; // $file is an instance of SplFileInfo
}
```

As a result, the finder returns instances of `SplFileInfo`.

If the directory does not exist, an `UnexpectedValueException` is thrown.

And what about searching for `*.txt` files in `$dir` including subdirectories? Instead of `in()`, use `from()`:

```php
foreach (Finder::findFiles('*.txt')->from($dir) as $file) {
	echo $file;
}
```

Search by more masks, even inside more directories within one iteration:

```php
foreach (Finder::findFiles('*.txt', '*.php')
	->in($dir1, $dir2) as $file) {
	...
}
```

Parameters can also be arrays:

```php
foreach (Finder::findFiles($masks)->in($dirs) as $file) {
	...
}
```

Searching for `*.txt` files containing a number in the name:

```php
foreach (Finder::findFiles('*[0-9]*.txt')->from($dir) as $file) {
	...
}
```

Searching for `*.txt` files, except those containing '`X`' in the name:

```php
foreach (Finder::findFiles('*.txt')
	->exclude('*X*')->from($dir) as $file) {
	...
}
```

`exclude()` is specified just after `findFiles()`, thus it applies to filename.


Directories to omit can be specified using the `exclude` **after** `from` clause:

```php
foreach (Finder::findFiles('*.php')
	->from($dir)->exclude('temp', '.git') as $file) {
	...
}
```

Here `exclude()` is after `from()`, thus it applies to the directory name.


And now something a bit more complicated: searching for `*.txt` files located in subdirectories starting with '`te`', but not '`temp`':

```php
foreach (Finder::findFiles('te*/*.txt')
	->exclude('temp*/*')->from($dir) as $file) {
	...
}
```

Depth of search can be limited using the `limitDepth()` method.



Searching for directories
-------------------------

In addition to files, it is possible to search for directories using `Finder::findDirectories('subdir*')`, or to search for files and directories: `Finder::find('file.txt')`.


Filtering
---------

You can also filter results. For example by size. This way we will traverse the files of size between 100B and 200B:


```php
foreach (Finder::findFiles('*.php')->size('>=', 100)->size('<=', 200)
	->from($dir) as $file) {
	...
}
```

Or files changed in the last two weeks:

```php
foreach (Finder::findFiles('*.php')->date('>', '- 2 weeks')
	->from($dir) as $file) {
	...
}
```

Here we traverse PHP files with number of lines greater than 1000. As a filter we use a custom callback:

```php
$finder = Finder::findFiles('*.php')->filter(function($file) {
	return count(file($file->getPathname())) > 1000;
})->from($dir);
```


Finder, find images larger than 50px × 50px:

```php
foreach (Finder::findFiles('*')
	->dimensions('>50', '>50')->from($dir) as $file) {
	...
}
```


Connection to Amazon S3
-----------------------

It's possible to use custom streams, for example Zend_Service_Amazon_S3:

```php
$s3 = new Zend_Service_Amazon_S3($key, $secret);
$s3->registerStreamWrapper('s3');

foreach (Finder::findFiles('photos*')
	->size('<=', 1e6)->in('s3://bucket-name') as $file) {
	echo $file;
}
```

Handy, right? You will certainly find a use for Finder in your applications.

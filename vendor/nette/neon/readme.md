[NEON](https://ne-on.org): Nette Object Notation
================================================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/neon.svg)](https://packagist.org/packages/nette/neon)
[![Tests](https://github.com/nette/neon/workflows/Tests/badge.svg?branch=master)](https://github.com/nette/neon/actions)
[![Coverage Status](https://coveralls.io/repos/github/nette/neon/badge.svg?branch=master)](https://coveralls.io/github/nette/neon?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/neon/v/stable)](https://github.com/nette/neon/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/neon/blob/master/license.md)


Introduction
============

NEON is a human-readable structured data format. In Nette, it is used for configuration files. It is also used for structured data such as settings, language translations, etc. [Try it on the sandbox](https://ne-on.org).

NEON stands for *Nette Object Notation*. It is less complex and ungainly than XML or JSON, but provides similar capabilities. It is very similar to YAML. The main advantage is that NEON has so-called [entities](#entities), thanks to which the configuration of DI services is so sexy. And allowes tabs for indentation.

NEON is built from the ground up to be simple to use.


[Support Neon](https://github.com/sponsors/dg)
----------------------------------------------

Do you like NEON? Are you looking forward to the new features?

[![Buy me a coffee](https://files.nette.org/icons/donation-3.svg)](https://github.com/sponsors/dg)

Thank you!


Usage
=====

Install via Composer:

```
composer require nette/neon
```

It requires PHP version 7.1 and supports PHP up to 8.0. Documentation can be found on the [website](https://doc.nette.org/neon).

`Neon::encode()` returns `$value` converted to NEON. As the second parameter you can use `Neon::BLOCK`, which will create multiline output.

```php
use Nette\Neon\Neon;

$neon = Neon::encode($value); // Returns $value converted to NEON
$neon = Neon::encode($value, Neon::BLOCK); // Returns $value converted to multiline NEON
```

`Neon::decode()` converts given NEON to PHP value:

```php
$value = Neon::decode('hello: world'); // Returns an array ['hello' => 'world']
```

Both methods throw `Nette\Neon\Exception` on error.


Integration
===========

- NetBeans (has built-in support)
- PhpStorm ([plugin](https://plugins.jetbrains.com/plugin/7060?pr))
- Visual Studio Code ([plugin](https://marketplace.visualstudio.com/items?itemName=Kasik96.latte))
- Sublime Text 3 ([plugin](https://github.com/FilipStryk/Nette-Latte-Neon-for-Sublime-Text-3))
- Sublime Text 2 ([plugin](https://github.com/Michal-Mikolas/Nette-package-for-Sublime-Text-2))


- [NEON for PHP](https://doc.nette.org/neon)
- [NEON for JavaScript](https://github.com/matej21/neon-js)
- [NEON for Python](https://github.com/paveldedik/neon-py).


Syntax
======

A file written in NEON usually consists of a sequence or mapping.


Mappings
--------
Mapping is a set of key-value pairs, in PHP it would be called an associative array. Each pair is written as `key: value`, a space after `:` is required. The value can be anything: string, number, boolean, null, sequence, or other mapping.

```neon
street: 742 Evergreen Terrace
city: Springfield
country: USA
```

In PHP, the same structure would be written as:

```php
[ // PHP
	'street' => '742 Evergreen Terrace',
	'city' => 'Springfield',
	'country' => 'USA',
]
```

This notation is called a block notation because all items are on a separate line and have the same indentation (none in this case). NEON also supports inline representation for mapping, which is enclosed in brackets, indentation plays no role, and the separator of each element is either a comma or a newline:

```neon
{street: 742 Evergreen Terrace, city: Springfield, country: USA}
```

This is the same written on multiple lines (indentation does not matter):

```neon
{
	street: 742 Evergreen Terrace
		city: Springfield, country: USA
}
```

Alternatively, `=` can be used instead of <code>: </code>, both in block and inline notation:

```neon
{street=742 Evergreen Terrace, city=Springfield, country=USA}
```


Sequences
---------
Sequences are indexed arrays in PHP. They are written as lines starting with the hyphen `-` followed by a space. Again, the value can be anything: string, number, boolean, null, sequence, or other mapping.

```neon
- Cat
- Dog
- Goldfish
```

In PHP, the same structure would be written as:

```php
[ // PHP
	'Cat',
	'Dog',
	'Goldfish',
]
```

This notation is called a block notation because all items are on a separate line and have the same indentation (none in this case). NEON also supports inline representation for sequences, which is enclosed in brackets, indentation plays no role, and the separator of each element is either a comma or a newline:

```neon
[Cat, Dog, Goldfish]
```

This is the same written on multiple lines (indentation does not matter):

```neon
[
	Cat, Dog
		Goldfish
]
```

Hyphens cannot be used in an inline representation.


Combination
-----------
Values of mappings and sequences may be other mappings and sequences. The level of indentation plays a major role. In the following example, the hyphen used to indicate sequence items has a greater indent than the `pets` key, so the items become the value of the first line:

```neon
pets:
 - Cat
 - Dog
cars:
 - Volvo
 - Skoda
```

In PHP, the same structure would be written as:

```php
[ // PHP
	'pets' => [
		'Cat',
		'Dog',
	],
	'cars' => [
		'Volvo',
		'Skoda',
	],
]
```

It is possible to combine block and inline notation:

```neon
pets: [Cat, Dog]
cars: [
	Volvo,
	Skoda,
]
```

Block notation can no longer be used inside an inline notation, this does not work:

```neon
item: [
	pets:
	 - Cat     # THIS IS NOT POSSIBLE!!!
	 - Dog
]
```

Because PHP uses the same structure for mapping and sequences, that is, arrays, both can be merged. The indentation is the same this time:

```neon
- Cat
street: 742 Evergreen Terrace
- Goldfish
```

In PHP, the same structure would be written as:

```php
[ // PHP
	'Cat',
	'street' => '742 Evergreen Terrace',
	'Goldfish',
]
```

Strings
-------
Strings in NEON can be enclosed in single or double quotes. But as you can see, they can also be without quotes.

```neon
- A unquoted string in NEON
- 'A singled-quoted string in NEON'
- "A double-quoted string in NEON"
```

If the string contains characters that can be confused with NEON syntax (hyphens, colons, etc.), it must be enclosed in quotation marks. We recommend using single quotes because they do not use escaping. If you need to enclose a quotation mark in such a string, double it:

```neon
'A single quote '' inside a single-quoted string'
```

Double quotes allow you to use escape sequences to write special characters using backslashes `\`. All escape sequences as in the JSON format are supported, plus `\_`, which is an non-breaking space, ie `\u00A0`.

```neon
- "\t \n \r \f \b \" \' \\ \/ \_"
- "\u00A9"
```

There are other cases where you need to enclose strings in quotation marks:
- they begin or end with spaces
- look like numbers, booleans, or null
- NEON would understand them as [dates](#dates)


Multiline strings
-----------------

A multiline string begins and ends with a triple quotation mark on separate lines. The indent of the first line is ignored for all lines:

```neon
'''
	first line
		second line
	third line
	'''
```

In PHP we would write the same as:

```php
"first line\n\tsecond line\nthird line" // PHP
```

Escaping sequences only work for strings enclosed in double quotes instead of apostrophes:

```neon
"""
	Copyright \u00A9
"""
```


Numbers
-------
NEON understands numbers written in so-called scientific notation and also numbers in binary, octal and hexadecimal:

```neon
- 12         # an integer
- 12.3       # a float
- +1.2e-34   # an exponential number

- 0b11010    # binary number
- 0o666      # octal number
- 0x7A       # hexa number
```

Nulls
-----
Null can be expressed in NEON by using `null` or by not specifying a value. Variants with a capital first or all uppercase letters are also allowed.

```neon
a: null
b:
```

Booleans
--------
Boolean values are expressed in NEON using `true` / `false` or `yes` / `no`. Variants with a capital first or all uppercase letters are also allowed.

```neon
[true, TRUE, True, false, yes, no]
```

Dates
-----
NEON uses the following formats to express data and automatically converts them to `DateTimeImmutable` objects:

```neon
- 2016-06-03                  # date
- 2016-06-03 19:00:00         # date & time
- 2016-06-03 19:00:00.1234    # date & microtime
- 2016-06-03 19:00:00 +0200   # date & time & timezone
- 2016-06-03 19:00:00 +02:00  # date & time & timezone
```


Entities
--------
An entity is a structure that resembles a function call:

```neon
Column(type: int, nulls: yes)
```

In PHP, it is parsed as an object [Nette\Neon\Entity](https://api.nette.org/3.0/Nette/Neon/Entity.html):

```php
// PHP
new Nette\Neon\Entity('Column', ['type' => 'int', 'nulls' => true])
```

Entities can also be chained:

```neon
Column(type: int, nulls: yes) Field(id: 1)
```

Which is parsed in PHP as follows:

```php
// PHP
new Nette\Neon\Entity(Nette\Neon\Neon::CHAIN, [
	new Nette\Neon\Entity('Column', ['type' => 'int', 'nulls' => true]),
	new Nette\Neon\Entity('Field', ['id' => 1]),
])
```

Inside the parentheses, the rules for inline notation used for mapping and sequences apply, so it can be divided into several lines and it is not necessary to add commas:

```neon
Column(
	type: int
	nulls: yes
)
```


Comments
--------
Comments start with `#` and all of the following characters on the right are ignored:

```neon
# this line will be ignored by the interpreter
street: 742 Evergreen Terrace
city: Springfield  # this is ignored too
country: USA
```


NEON versus JSON
================
JSON is a subset of NEON. Each JSON can therefore be parsed as NEON:

```neon
{
"php": {
	"date.timezone": "Europe\/Prague",
	"zlib.output_compression": true
},
"database": {
	"driver": "mysql",
	"username": "root",
	"password": "beruska92"
},
"users": [
	"Dave", "Kryten", "Rimmer"
]
}
```

What if we could omit quotes?

```neon
{
php: {
	date.timezone: Europe/Prague,
	zlib.output_compression: true
},
database: {
	driver: mysql,
	username: root,
	password: beruska92
},
users: [
	Dave, Kryten, Rimmer
]
}
```

How about braces and commas?

```neon
php:
	date.timezone: Europe/Prague
	zlib.output_compression: true

database:
	driver: mysql
	username: root
	password: beruska92

users: [
	Dave, Kryten, Rimmer
]
```

Are bullets more legible?

```neon
php:
	date.timezone: Europe/Prague
	zlib.output_compression: true

database:
	driver: mysql
	username: root
	password: beruska92

users:
	- Dave
	- Kryten
	- Rimmer
```

How about comments?

```neon
# my web application config

php:
	date.timezone: Europe/Prague
	zlib.output_compression: true  # use gzip

database:
	driver: mysql
	username: root
	password: beruska92

users:
	- Dave
	- Kryten
	- Rimmer
```

You found NEON syntax!

If you like NEON, **[please make a donation now](https://github.com/sponsors/dg)**. Thank you!

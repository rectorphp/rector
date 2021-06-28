# PHP Enum implementation inspired from SplEnum

[![Build Status](https://travis-ci.org/myclabs/php-enum.png?branch=master)](https://travis-ci.org/myclabs/php-enum)
[![Latest Stable Version](https://poser.pugx.org/myclabs/php-enum/version.png)](https://packagist.org/packages/myclabs/php-enum)
[![Total Downloads](https://poser.pugx.org/myclabs/php-enum/downloads.png)](https://packagist.org/packages/myclabs/php-enum)
[![psalm](https://shepherd.dev/github/myclabs/php-enum/coverage.svg)](https://shepherd.dev/github/myclabs/php-enum)

Maintenance for this project is [supported via Tidelift](https://tidelift.com/subscription/pkg/packagist-myclabs-php-enum?utm_source=packagist-myclabs-php-enum&utm_medium=referral&utm_campaign=readme).

## Why?

First, and mainly, `SplEnum` is not integrated to PHP, you have to install the extension separately.

Using an enum instead of class constants provides the following advantages:

- You can use an enum as a parameter type: `function setAction(Action $action) {`
- You can use an enum as a return type: `function getAction() : Action {`
- You can enrich the enum with methods (e.g. `format`, `parse`, …)
- You can extend the enum to add new values (make your enum `final` to prevent it)
- You can get a list of all the possible values (see below)

This Enum class is not intended to replace class constants, but only to be used when it makes sense.

## Installation

```
composer require myclabs/php-enum
```

## Declaration

```php
use MyCLabs\Enum\Enum;

/**
 * Action enum
 */
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
```

## Usage

```php
$action = Action::VIEW();

// or with a dynamic key:
$action = Action::$key();
// or with a dynamic value:
$action = Action::from($value);
// or
$action = new Action($value);
```

As you can see, static methods are automatically implemented to provide quick access to an enum value.

One advantage over using class constants is to be able to use an enum as a parameter type:

```php
function setAction(Action $action) {
    // ...
}
```

## Documentation

- `__construct()` The constructor checks that the value exist in the enum
- `__toString()` You can `echo $myValue`, it will display the enum value (value of the constant)
- `getValue()` Returns the current value of the enum
- `getKey()` Returns the key of the current value on Enum
- `equals()` Tests whether enum instances are equal (returns `true` if enum values are equal, `false` otherwise)

Static methods:

- `from()` Creates an Enum instance, checking that the value exist in the enum
- `toArray()` method Returns all possible values as an array (constant name in key, constant value in value)
- `keys()` Returns the names (keys) of all constants in the Enum class
- `values()` Returns instances of the Enum class of all Enum constants (constant name in key, Enum instance in value)
- `isValid()` Check if tested value is valid on enum set
- `isValidKey()` Check if tested key is valid on enum set
- `assertValidValue()` Assert the value is valid on enum set, throwing exception otherwise
- `search()` Return key for searched value

### Static methods

```php
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}

// Static method:
$action = Action::VIEW();
$action = Action::EDIT();
```

Static method helpers are implemented using [`__callStatic()`](http://www.php.net/manual/en/language.oop5.overloading.php#object.callstatic).

If you care about IDE autocompletion, you can either implement the static methods yourself:

```php
final class Action extends Enum
{
    private const VIEW = 'view';

    /**
     * @return Action
     */
    public static function VIEW() {
        return new Action(self::VIEW);
    }
}
```

or you can use phpdoc (this is supported in PhpStorm for example):

```php
/**
 * @method static Action VIEW()
 * @method static Action EDIT()
 */
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
```

## Related projects

- [Doctrine enum mapping](https://github.com/acelaya/doctrine-enum-type)
- [Symfony ParamConverter integration](https://github.com/Ex3v/MyCLabsEnumParamConverter)
- [PHPStan integration](https://github.com/timeweb/phpstan-enum)
- [Yii2 enum mapping](https://github.com/KartaviK/yii2-enum)

# PHPStan PHPUnit extensions and rules

[![Build](https://github.com/phpstan/phpstan-phpunit/workflows/Build/badge.svg)](https://github.com/phpstan/phpstan-phpunit/actions)
[![Latest Stable Version](https://poser.pugx.org/phpstan/phpstan-phpunit/v/stable)](https://packagist.org/packages/phpstan/phpstan-phpunit)
[![License](https://poser.pugx.org/phpstan/phpstan-phpunit/license)](https://packagist.org/packages/phpstan/phpstan-phpunit)

* [PHPStan](https://phpstan.org/)
* [PHPUnit](https://phpunit.de)

This extension provides following features:

* `createMock()`, `getMockForAbstractClass()` and `getMockFromWsdl()` methods return an intersection type (see the [detailed explanation of intersection types](https://phpstan.org/blog/union-types-vs-intersection-types)) of the mock object and the mocked class so that both methods from the mock object (like `expects`) and from the mocked class are available on the object.
* `getMock()` called on `MockBuilder` is also supported.
* Interprets `Foo|PHPUnit_Framework_MockObject_MockObject` in phpDoc so that it results in an intersection type instead of a union type.
* Defines early terminating method calls for the `PHPUnit\Framework\TestCase` class to prevent undefined variable errors.
* Specifies types of expressions passed to various `assert` methods like `assertInstanceOf`, `assertTrue`, `assertInternalType` etc.
* Combined with PHPStan's level 4, it points out always-true and always-false asserts like `assertTrue(true)` etc.

It also contains this strict framework-specific rules (can be enabled separately):

* Check that you are not using `assertSame()` with `true` as expected value. `assertTrue()` should be used instead.
* Check that you are not using `assertSame()` with `false` as expected value. `assertFalse()` should be used instead.
* Check that you are not using `assertSame()` with `null` as expected value. `assertNull()` should be used instead.
* Check that you are not using `assertSame()` with `count($variable)` as second parameter. `assertCount($variable)` should be used instead.

## How to document mock objects in phpDocs?

If you need to configure the mock even after you assign it to a property or return it from a method, you should add `PHPUnit_Framework_MockObject_MockObject` to the phpDoc:

```php
/**
 * @return Foo&PHPUnit_Framework_MockObject_MockObject
 */
private function createFooMock()
{
	return $this->createMock(Foo::class);
}

public function testSomething()
{
	$fooMock = $this->createFooMock();
	$fooMock->method('doFoo')->will($this->returnValue('test'));
	$fooMock->doFoo();
}
```

Please note that the correct syntax for intersection types is `Foo&PHPUnit_Framework_MockObject_MockObject`. `Foo|PHPUnit_Framework_MockObject_MockObject` is also supported, but only for ecosystem and legacy reasons.

If the mock is fully configured and only the methods of the mocked class are supposed to be called on the value, it's fine to typehint only the mocked class:

```php
/** @var Foo */
private $foo;

protected function setUp()
{
	$fooMock = $this->createMock(Foo::class);
	$fooMock->method('doFoo')->will($this->returnValue('test'));
	$this->foo = $fooMock;
}

public function testSomething()
{
	$this->foo->doFoo();
	// $this->foo->method() and expects() can no longer be called
}
```


## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev phpstan/phpstan-phpunit
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```
includes:
    - vendor/phpstan/phpstan-phpunit/extension.neon
```

To perform framework-specific checks, include also this file:

```
    - vendor/phpstan/phpstan-phpunit/rules.neon
```

</details>

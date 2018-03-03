# Rector - Upgrade your Legacy App to Modern Codebase

Rector is under development phase in 2018, to figure out the best way to use it in applications, polish API of Rector classes and get feedback from community. Please consider this while using it and report issues or post ideas you'll come up with while using. Thank you!

When you're gonna move from manual work to **instant upgrades**?

[![Build Status](https://img.shields.io/travis/rectorphp/rector/master.svg?style=flat-square)](https://travis-ci.org/rectorphp/rector)
[![Coverage Status](https://img.shields.io/coveralls/RectorPHP/Rector/master.svg?style=flat-square)](https://coveralls.io/github/rectorphp/rector?branch=master)

![Rector-showcase](docs/images/rector-showcase.gif)

Rector **upgrades your application** for you, with focus on open-source projects:

<p align="center">
    <a href="/src/config/level/symfony"><img src="/docs/images/symfony.png"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/src/config/level/nette"><img src="/docs/images/nette.png" height="50"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/src/config/level/phpunit"><img src="/docs/images/phpunit.jpg"></a>
    <img src="/docs/images/space.png" width=20>
    <a href="/src/config/level/twig"><img src="/docs/images/twig.png"></a>
</p>


Rector can:

- [Rename classes](#replace-a-class-name)
- [Rename class' methods](#change-a-method-name)
- [Rename partial namespace](#eplace-some-part-of-the-namespace)
- [Rename pseudo-namespace to namespace](#replace-the-underscore-naming-_-with-namespaces-)
- [Add, replace or remove arguments](#change-a-argument-value)
- [Add typehints based on new types of parent class or interface](#remove-a-value-object-and-use-simple-type)
- And much more...

## Install

```bash
composer require --dev rector/rector:'dev-master' nikic/php-parser:'4.0.x-dev'
```

### Do you Have Conflicts?

It may also happen your dependencies are in conflict, e.g. [PHPStan](https://github.com/phpstan/phpstan) requires [PHP-Parser](https://github.com/nikic/PHP-Parser) version 3, or older PHPUnit etc. This might be solved in the future, when PHP-Parser version 4 becomes stable.

Since Rector **uses project's autoload to analyze type of elements**, it cannot be installed as project in standalone directory but **needs to be added as dependency**. Here [`bamarni/composer-bin-plugin`](https://github.com/bamarni/composer-bin-plugin) becomes useful:

```bash
composer require bamarni/composer-bin-plugin --dev
```

Then, require Rector using `composer bin`:

```bash
composer bin rector require --dev rector/rector:'dev-master' nikic/php-parser:'4.0.x-dev'
```

And Rector is accessible as:

```bash
vendor/bin/rector
```

### Extra Autoloading

Rector relies on project and autoloading of its classes. To specify own autoload file, use `--autoload-file` option:

```bash
vendor/bin/rector process ../project --autoload-file ../project/vendor/autoload.php
```

## How to Reconstruct your Code

### A. Prepared Sets

Featured open-source projects have **prepared sets**. You'll find them in [`/src/config/level`](/src/config/level).

Do you need to upgrade to **Symfony 4.0**, for example?

1. Run rector on your `/src` directory:

    ```bash
    vendor/bin/rector process src --level symfony40
    ```

    Which is a shortcut for using complete path with `--config` option:

    ```bash
    vendor/bin/rector process src --config vendor/rector/rector/src/config/level/symfony/symfony40.yml
    ```

    You can also use your **own config file**:

    ```bash
    vendor/bin/rector process src --config your-own-config.yml
    ```

2. Do you want to see the preview of changes first?

    Use the `--dry-run` option:

    ```bash
    vendor/bin/rector process src --level symfony33 --dry-run
    ```

### B. Custom Sets

1. Create `rector.yml` with desired Rectors:

    ```yml
    rectors:
        Rector\Rector\Contrib\Nette\Application\InjectPropertyRector: ~
    ```

2. Try Rector on your `/src` directory:

    ```bash
    vendor/bin/rector process src --dry-run
    ```

3. Apply the changes if you like them:

    ```bash
    vendor/bin/rector process src
    ```

## Simple setup with Dynamic Rectors

You don't have to always write PHP code. Many projects change only classes or method names, so it would be too much work for a simple task.

Instead you can use prepared **Dynamic Rectors** directly in `*.yml` config:

You can:

### Replace a class name

```yml
# phpunit60.yml
services:
    Rector\Rector\Dynamic\ClassReplacerRector:
        $oldToNewClasses:
            # old class: new class
            'PHPUnit_Framework_TestCase': 'PHPUnit\Framework\TestCase'
```

### Replace some part of the namespace

```yml
# better-reflection20.yml
services:
    Rector\Rector\Dynamic\NamespaceReplacerRector:
        $oldToNewNamespaces:
            # old namespace: new namespace
            'BetterReflection': 'Roave\BetterReflection'
```

### Change a method name

```yml
services:
    Rector\Rector\Dynamic\MethodNameReplacerRector:
        $perClassOldToNewMethods:
            # class
            'Nette\Utils\Html':
                # old method: new method
                'add': 'addHtml'

            # or in case of static methods calls

            # class
            'Nette\Bridges\FormsLatte\FormMacros':
                # old method: [new class, new method]
                'renderFormBegin': ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin']
```

### Change a property name

```yml
services:
    Rector\Rector\Dynamic\PropertyNameReplacerRector:
        $perClassOldToNewProperties:
            # class:
            'PhpParser\Node\Param':
                # old property: new property
                'name': 'var'
```

### Change a class constant name

```yml
services:
    Rector\Rector\Dynamic\ClassConstantReplacerRector:
        $oldToNewConstantsByClass:
            # class
            'Symfony\Component\Form\FormEvents':
                # old constant: new constant
                'PRE_BIND': 'PRE_SUBMIT'
                'BIND': 'SUBMIT'
                'POST_BIND': 'POST_SUBMIT'
```

### Change parameters type hinting according to the parent type

```yml
services:
    Rector\Rector\Dynamic\ParentTypehintedArgumentRector:
        $typehintForArgumentByMethodAndClass:
            # class
            'PhpParser\Parser':
                # method
                'parse':
                    # parameter: typehint
                    'code': 'string'
```

### Change argument value or remove argument

```yml
services:
    Rector\Rector\Dynamic\ArgumentReplacerRector:
        $argumentChangesByMethodAndType:
            -
                class: 'Symfony\Component\DependencyInjection\ContainerBuilder'
                method: 'compile'
                position: 0
                # change default value
                type: 'changed'
                default_value: false

                # or remove
                type: 'removed'

                # or replace old default value by new one
                type: 'replace_default_value'
                replace_map:
                    'Symfony\Component\DependencyInjection\ContainerBuilder::SCOPE_PROTOTYPE': false
```

### Replace the underscore naming `_` with namespaces `\`

```yml
services:
    Rector\Rector\Dynamic\PseudoNamespaceToNamespaceRector:
        $configuration:
            # old namespace prefix
            - 'PHPUnit_'
            # exclude classes
            - '!PHPUnit_Framework_MockObject_MockObject'
```

### Modify a property to method

```yml
services:
    Rector\Rector\Dynamic\PropertyToMethodRector:
        $perClassPropertyToMethods:
            # type
            'Symfony\Component\Translation\Translator':
                # property to replace
                'locale':
                    # (prepared key): get method name
                    'get': 'getLocale'
                    # (prepared key): set method name
                    'set': 'setLocale'
```

### Remove a value object and use simple type

```yml
services:
    Rector\Rector\Dynamic\ValueObjectRemoverRector:
        $valueObjectsToSimpleTypes:
            # type: new simple type
            'ValueObject\Name': 'string'
```

For example:

```diff
- $value = new ValueObject\Name('Tomas');
+ $value = 'Tomas';
```

```diff
/**
-* @var ValueObject\Name
+* @var string
 */
private $name;
```

```diff
- public function someMethod(ValueObject\Name $name) { ...
+ public function someMethod(string $name) { ...
```

## Replace Property and Method Annotations

```yml
services:
    Rector\Rector\Dynamic\AnnotationReplacerRector:
        $classToAnnotationMap:
            # type
            PHPUnit\Framework\TestCase:
                # old annotation: new annotation
                scenario: test
```

```diff
 final class SomeTest extends PHPUnit\Framework\TestCase
 {
     /**
-     * @scenario
+     * @test
      */
     public function test()
     {
     }
 }
```

## Turn Magic to Methods

### Replace `get/set` magic methods with real ones

```yml
services:
    Rector\Rector\MagicDisclosure\GetAndSetToMethodCallRector:
        $typeToMethodCalls:
            # class
            'Nette\DI\Container':
                # magic method (prepared keys): new real method
                'get': 'getService'
                'set': 'addService'
```

For example:

```diff
- $result = $container['key'];
+ $result = $container->getService('key');
```

```diff
- $container['key'] = $value;
+ $container->addService('key', $value);
```

### Replace `isset/unset` magic methods with real ones

```yml
services:
    Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector:
        $typeToMethodCalls:
            # class
            'Nette\DI\Container':
                # magic method (prepared keys): new real method
                'isset': 'hasService'
                'unset': 'removeService'
```

For example:

```diff
- isset($container['key']);
+ $container->hasService('key');
```

```diff
- unset($container['key']);
+ $container->removeService('key');
```

### Replace `toString` magic method with real one

```yml
services:
    Rector\Rector\MagicDisclosure\ToStringToMethodCallRector:
        $typeToMethodCalls:
            # class
            'Symfony\Component\Config\ConfigCache':
                # magic method (prepared key): new real method
                'toString': 'getPath'
```

For example:

```diff
- $result = (string) $someValue;
+ $result = $someValue->getPath();
```

```diff
- $result = $someValue->__toString();
+ $result = $someValue->getPath();
```

### Remove [Fluent Interface](https://ocramius.github.io/blog/fluent-interfaces-are-evil/)

```yml
services:
    Rector\Rector\Dynamic\FluentReplaceRector: ~
```

```diff
 class SomeClass
 {
     public function setValue($value)
     {
         $this->value = $value;
-        return $this;
     }

     public function setAnotherValue($anontherValue)
     {
         $this->anotherValue = $anotherValue;
-        return $this;
     }
 }

 $someClass = new SomeClass();
- $someClass->setValue(5)
+ $someClass->setValue(5);
-     ->setAnotherValue(10);
+ $someClass->setAnotherValue(10);
 }
```

## Coding Standards are Outsourced

This package has no intention in formatting your code, as **coding standard tools handle this much better**. We prefer [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) with Rector's prepared set:

```bash
# install
composer require --dev symplify/easy-coding-standard

# check
vendor/bin/ecs check --config vendor/rector/rector/ecs-after-rector.neon

# fix
vendor/bin/ecs check --config vendor/rector/rector/ecs-after-rector.neon --fix
```

## More Detailed Documentation

- [How Rector Works?](/docs/HowItWorks.md)
- [How to Create Rector with Fluent Builder](/docs/FluentBuilderRector.md)
- [How to Create Own Rector](/docs/HowToCreateOwnRector.md)
- [Service Name to Type Provider](/docs/ServiceNameToTypeProvider.md)

## How to Contribute

Just follow 3 rules:

- **1 feature per pull-request**
- **New feature needs tests**
- Tests, coding standards and PHPStan **checks must pass**:

    ```bash
    composer complete-check
    ```

    Don you need to fix coding standards? Run:

    ```bash
    composer fix-cs
    ```

We would be happy to merge your feature then.

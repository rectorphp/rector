# How to use Dynamic Rectors?

Dynamic Rectors are classes, that are prepared to do specific operation. You can configure them with list of options, so they modify the case you need.

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
    Rector\Rector\Dynamic\ArgumentRector:
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

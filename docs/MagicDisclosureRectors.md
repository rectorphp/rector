# How to turn Magic to Explicit with Rectors?

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

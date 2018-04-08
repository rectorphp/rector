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
- $result = $anotherService['key'];
+ $result = $anotherService->getService('key');
```

```diff
- $anotherService['key'] = $value;
+ $anotherService->addService('key', $value);
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
- isset($anotherService['key']);
+ $anotherService->hasService('key');
```

```diff
- unset($anotherService['key']);
+ $anotherService->removeService('key');
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


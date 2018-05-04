# Reflection DocBlock

This package takes care of Fully Qualified names from annotations:

This code:

```php
namespace SomeNamespace;

...

/**
 * @param SomeType $value
 */
public function __construct($value)
{
}
```

will produce this:

```php
$valueNodeTypes = $valueNode->getAttribute(Attributes::TYPES);
var_dump($valueNodeTypes); // ['SomeNamespace\SomeType'];
```

### Inspiration

- [Roave\BetterReflection - TypesFinder](https://github.com/Roave/BetterReflection/tree/master/src/TypesFinder)

- [Roave/BetterReflection - NamespaceNodeToReflectionTypeContext](https://github.com/Roave/BetterReflection/blob/master/src/TypesFinder/PhpDocumentor/NamespaceNodeToReflectionTypeContext.php)

- [phpstan/phpstan - NameScope](https://github.com/phpstan/phpstan/blob/master/src/Analyser/NameScope.php)

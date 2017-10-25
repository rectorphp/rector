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

- [Roave\BetterReflection](https://github.com/Roave/BetterReflection/tree/master/src/TypesFinder)


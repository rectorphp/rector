# Node Type Resolver

This package detects **class, interface and trait types** for classes, variables and properties. Those types and added via `setAttribute(Attribute::TYPES)`, so you always now where you are.

Anonymous classes are included, e.g.:

```php
$someAnonymousClass = new class extends SomeClass
{
}
```

includes types:

```php
['SomeClass']
```


## How it works?

1. Traverse all nodes
2. Detect variable assigns, property use, method arguments
3. Resolve types 
4. Add them via `$node->setAttribute(Attribute::TYPE, $type);` to ever node


## How it helps you?

### 1. You can get `class`

```php
$class = (string) $node->getAttribute(Attribute::CLASS_NAME);

if (Strings::endsWith($class, 'Command')) {
    // we are in Command class
}

// to be sure it's console command

/** @var PhpParser\Node\Name\FullyQualified $fqnName */
$classNode = $node->getAttribute(Attribute::CLASS_NODE);

$fqnName = $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);

if ($fqnName->toString() === 'Symfony\Component\Console\Command') {
    // we are sure it's child of Symfony\Console Command class
}
```

### 2. Or `type` attribute:

```php
/** @var string[] $nodeTypes */
$nodeTypes = $node->var->getAttribute(Attribute::TYPES);

if (in_array('Nette\Application\UI\Form', $nodeTypes, true) {
    // this is Nette\Application\UI\Form variable
}
```

...in any Rector you create.


### Inspiration

- https://github.com/Roave/BetterReflection/pull/224
- https://github.com/phpactor/worse-reflection/blob/master/lib/Core/Inference/SymbolInformationResolver.php
# Node Type Resolver

This package detects `class`, type `class_node` and `$variable` or `$this->property` types and adds them to all relevant nodes.

Class type is added to all nodes inside it, so you always now where you are. 

Anonymous classes are skipped.


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
/** @var string $type */
$type = $node->var->getAttribute(Attribute::TYPE);

if ($type === 'Nette\Application\UI\Form') {
    // this is Nette\Application\UI\Form variable
}
```

...in any Rector you create.

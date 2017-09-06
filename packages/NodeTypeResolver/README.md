# Node Type Resolver

This package detects `class`, type `class_node` and `$variable` or `$this->property` types and adds them to all relevant nodes.

Class type is added to all nodes inside it, so you always now where you are. 

Anonymous classes are skipped.


## How it works?

1. Traverse all nodes
2. Detect variable assigns, property use, method arguments
3. Resolve types 
4. Add them via `$node->setAttribute('type', $type);` to ever node


## How it helps you?

You can get `class` 

```php
$class = (string) $node->getAttribute('class');

if (Strings::endsWith($class, 'Command')) {
    // we are in Command class
}

// to be sure it's console command

/** @var PhpParser\Node\Name\FullyQualified $fqnName */
$classNode = $node->getAttribute('class_node');

$fqnName = $classNode->extends->getAttribute('resolvedName');

if ($fqnName->toString() === 'Symfony\Component\Console\Command') {
    // we are sure it's child of Symfony\Console Command class
}
```

or `type` attribute:

```php
/** @var string $type */
$type = $node->var->getAttribute('type');

if ($type === 'Nette\Application\UI\Form') {
    // this is Nette\Application\UI\Form variable
}
```

...in any Rector you create.
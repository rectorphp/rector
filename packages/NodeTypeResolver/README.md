# Node Type Resolver

This package detects **class, interface and trait types** for classes, variables and properties. Those types are resolved by `NodeTypeResolver` service.

Includes also:

- Anonymous classes
- Traits of parent classes

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

### 2. Get Types of Certain Elements

```php
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SomeRector
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function refactor(Node $node): ?Node
    {
        /** @var string[] $nodeTypes */
        $nodeTypes = $this->nodeTypeResolver->resolve($node);

        if (in_array('Nette\Application\UI\Form', $nodeTypes, true) {
            // this is Nette\Application\UI\Form variable
        }
    }
}
```

...in any Rector you create.

### Inspiration

@todo phpstan + silverstripe

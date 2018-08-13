# Node Type Resolver

This package detects **class, interface and trait types** for classes, variables and properties. Those types are resolved by `NodeTypeResolver` service. It uses PHPStan for `PhpParser\Node\Expr` nodes and own type resolvers for other nodes like `PhpParser\Node\Stmt\Class_`, `PhpParser\Node\Stmt\Interface_` or `PhpParser\Node\Stmt\Trait_`.


## Install

```bash
composer require rector/node-type-resolver
```

You first need to integrate `Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator` to you application. It will traverse nodes and decorate with attributes that you can use right away and also attributes that are required for  `Rector\NodeTypeResolver\NodeTypeResolver`.

This package works best in Symfony Kernel application, but is also available in standalone use thanks to decoupled container factory.

### A. Symfony Application

Import `services.yml` in your Symfony config:

```yaml
# your-app/config.yml
imports:
    - { resource: 'vendor/rector/node-type-resolver/config/services.yml' }
```

Require `Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator` in the constructor:

```php
<?php declare(strict_types=1);

namespace YourApp;

use PhpParser\Parser;
use Rector\NodeTypeResolver\Node\MetadataAttribute;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;

final class SomeClass
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    public function __construct(
        Parser $parser,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator
    ) {
        $this->parser = $parser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }

    public function run(): void
    {
        $someFilePath = __DIR__ . '/SomeFile.php';
        $nodes = $this->parser->parse(file_get_contents($someFilePath));

        $decoratedNodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($nodes, $someFilePath);

        foreach ($decoratedNodes as $node) {
            $className = $node->getAttribute(MetadataAttribute::CLASS_NAME);
            // "string" with class name
            var_dump($className);
        }

        // do whatever you need :)
    }
}
```

### B. Standalone PHP Code

```php
<?php declare(strict_types=1);

use Rector\NodeTypeResolver\DependencyInjection\NodeTypeResolverContainerFactory;
use Rector\NodeTypeResolver\Node\MetadataAttribute;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use PhpParser\ParserFactory;

$phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

$someFilePath = __DIR__ . '/SomeFile.php';
$nodes = $phpParser->parse(file_get_contents($someFilePath));

$nodeTypeResolverContainer = (new NodeTypeResolverContainerFactory())->create();
/** @var NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator */
$nodeScopeAndMetadataDecorator = $nodeTypeResolverContainer->get(NodeScopeAndMetadataDecorator::class);
$decoratedNodes = $nodeScopeAndMetadataDecorator->decorateNodesFromFile($nodes, $someFilePath);

foreach ($decoratedNodes as $node) {
    $className = $node->getAttribute(MetadataAttribute::CLASS_NAME);
    // "string" with class name
    var_dump($className);
}
```


---

## Usage

After this integration you have new attributes you can work with.

### Attributes

These attributes are always available anywhere inside the Node tree. That means that `CLASS_NAME` is available **in every node that is in the class**. That way you can easily get class name on `Property` node.

#### Namespaces

```php
<?php declare(strict_types=1);

//@todo examples of dump

use Rector\NodeTypeResolver\Node\MetadataAttribute;

// string name of current namespace
$namespaceName = $node->setAttribute(MetadataAttribute::NAMESPACE_NAME, $this->namespaceName);

// instance of "PhpParser\Node\Stmt\Namespace_"
$namespaceNode = $node->setAttribute(MetadataAttribute::NAMESPACE_NODE, $this->namespaceNode);

// instances of "PhpParser\Node\Stmt\Use_"
$useNodes = $node->setAttribute(MetadataAttribute::USE_NODES, $this->useNodes);
```

#### Classes

```php
<?php declare(strict_types=1);

//@todo examples of dump

use Rector\NodeTypeResolver\Node\MetadataAttribute;

// string name of current class
$className = $node->getAttribute(MetadataAttribute::CLASS_NAME);

// instance of "PhpParser\Node\Stmt\Class_"
$classNode = $node->getAttribute(MetadataAttribute::CLASS_NODE);

// string name of current class
$parentClassName = $node->getAttribute(MetadataAttribute::PARENT_CLASS_NAME);
```

#### Methods

```php
<?php declare(strict_types=1);

use Rector\NodeTypeResolver\Node\MetadataAttribute;

//@todo examples of dump

// string name of current method
$methodName = $node->getAttribute(MetadataAttribute::METHOD_NAME);

// instance of "PhpParser\Node\Stmt\ClassMethod"
$methodNode = $node->getAttribute(MetadataAttribute::METHOD_NODE);

// string name of current method call ($this->get => "get")
$methodCallName = $node->getAttribute(MetadataAttribute::METHOD_NAME);
```

### Get Types of Node

`Rector\NodeTypeResolver\NodeTypeResolver` helps you detect object types for any node that can have one.

Get it via constructor or `$container->get(Rector\NodeTypeResolver\NodeTypeResolver::class)`;

```php
<?php declare(strict_types=1);

use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\NodeTypeResolver;

// previously processed nodes
$nodes = [...];
/** @var NodeTypeResolver $nodeTypeResolver */
$nodeTypeResolver = ...;

foreach ($nodes as $node) {
    if ($node instanceof Class_) {
        $classNodeTypes = $nodeTypeResolver->resolve($node);
        var_dump($classNodeTypes); // array of strings
    }
}
```

### Inspiration

Huge thanks for inspiration of this integration belongs to [PHPStanScopeVisitor](https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php) by [SilverStripe](https://github.com/silverstripe/) - Thank you ❤️️ !


# Node Type Resolver

This package detects **class, interface and trait types** for classes, variables and properties. Those types are resolved by `NodeTypeResolver` service. It uses PHPStan for `PhpParser\Node\Expr` nodes and own type resolvers for other nodes like `PhpParser\Node\Stmt\Class_`, `PhpParser\Node\Stmt\Interface_` or `PhpParser\Node\Stmt\Trait_`.  

## Install

```bash
composer require rector/node-type-resolver
```

## Usage

### 1. Get Types of Node

```php
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SomeNodeProcessor
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function process(Node $node)
    {
        /** @var string[] $nodeTypes */
        $nodeTypes = $this->nodeTypeResolver->resolve($node);

        if (in_array('Nette\Application\UI\Form', $nodeTypes, true) {
            // this is Nette\Application\UI\Form variable
        }
        
        // continue
    }
}
```

...in any Rector you create.


### 2. Helper Attributes

These attributes are always available anywhere inside the Node tree. That means that `CLASS_NAME` is available **in every node that is in the class**. That way you can easily get class name on `Property` node.

#### Namespaces

```php
<?php declare(strict_types=1);

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

// string name of current method
$methodName = $node->getAttribute(MetadataAttribute::METHOD_NAME);

// instance of "PhpParser\Node\Stmt\ClassMethod"
$methodNode = $node->getAttribute(MetadataAttribute::METHOD_NODE);

// string name of current method call ($this->get => "get")
$methodCallName = $node->getAttribute(MetadataAttribute::METHOD_NAME);
```

#### Setup

1. Import `services.yml` in your config

```yaml
# your-app/config.yml
imports:
    - { resource: 'vendor/rector/node-type-resolver/config/services.yml' }
```

2. Add NodeVisitors to your NodeTraverse in config  

```yaml
# your-app/config.yml
services:
    YourApp\NodeTraverser:
        calls:
            // @todo depends on NameResolver from PhpParser - or resolve instead of it?
            // @todo maybe add MetadataNodeVisitor and decouple these 2 into services, that would fix
            // the previous issue
            - ['addNodeVisitor', ['@Rector\NodeTypeResolver\NodeVisitor\ClassAndMethodResolver']]
            - ['addNodeVisitor', ['@Rector\NodeTypeResolver\NodeVisitor\NamespaceResolver']]        
```

or if you create NodeTraverser in a factory:

```php
<?php declare(strict_types=1);

namespace YourApp;

use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\NodeVisitor\ClassAndMethodResolver;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceResolver;

final class NodeTraverserFactory
{
    /**
     * @var ClassAndMethodResolver
     */
    private $classAndMethodResolver;
    
    /**
     * @var NamespaceResolver  
     */
    private $namespaceResolver;
    
    public function __construct(
        ClassAndMethodResolver $classAndMethodResolver, 
        NamespaceResolver $namespaceResolver
    ) {
        $this->classAndMethodResolver = $classAndMethodResolver;
        $this->namespaceResolver = $namespaceResolver;
    }
    
    public function create(): NodeTraverser
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->namespaceResolver);
        $nodeTraverser->addVisitor($this->classAndMethodResolver);
        
        // your own NodeVisitors
        $nodeTraverser->addVisitor(...);
        
        return $nodeTraverser;
    }
}
```

3. And get attributes anywhere you need it

```php
<?php declare(strict_types=1);

use Rector\NodeTypeResolver\Node\MetadataAttribute;

/** @var PhpParser\NodeTraverser $nodeTraverser */
$nodeTraverser = ...; // from DI container or manually created

$nodes = $this->parser->parseFile(...);
$nodes = $nodeTraverser->traverse($nodes);

/** @var PhpParser\Node $node */
foreach ($nodes as $node) {
    $className = $node->getAttribute(MetadataAttribute::CLASS_NAME);
    var_dump($className);
}
```

3. Add CompilerPass to your Kernel

```php
<?php declare(strict_types=1);

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Rector\NodeTypeResolver\DependencyInjection\CompilerPass\NodeTypeResolverCollectorCompilerPass;

class AppKernel extends Kernel
{
    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new NodeTypeResolverCollectorCompilerPass());
    }
} 
```

And that's it!

### Inspiration

- [PHPStanScopeVisitor](https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php) in [SilverStripe](https://github.com/silverstripe/) - Thank you ❤️️  

<?php

declare (strict_types=1);
namespace Rector\Privatization\NodeReplacer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Privatization\Naming\ConstantNaming;
use RectorPrefix20210509\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class PropertyFetchWithConstFetchReplacer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @var ConstantNaming
     */
    private $constantNaming;
    /**
     * @var NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20210509\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Privatization\Naming\ConstantNaming $constantNaming, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->constantNaming = $constantNaming;
        $this->nodeFactory = $nodeFactory;
    }
    public function replace(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Property $property) : void
    {
        $propertyProperty = $property->props[0];
        $propertyName = $this->nodeNameResolver->getName($property);
        $constantName = $this->constantNaming->createFromProperty($propertyProperty);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class, function (\PhpParser\Node $node) use($propertyName, $constantName) : ?ClassConstFetch {
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetch($node)) {
                return null;
            }
            /** @var PropertyFetch|StaticPropertyFetch $node */
            if (!$this->nodeNameResolver->isName($node->name, $propertyName)) {
                return null;
            }
            // replace with constant fetch
            return $this->nodeFactory->createSelfFetchConstant($constantName, $node);
        });
    }
}

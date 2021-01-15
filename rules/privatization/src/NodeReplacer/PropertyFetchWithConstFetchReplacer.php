<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeReplacer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\Naming\ConstantNaming;

final class PropertyFetchWithConstFetchReplacer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var ConstantNaming
     */
    private $constantNaming;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        ConstantNaming $constantNaming
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->constantNaming = $constantNaming;
    }

    public function replace(Node\Stmt\Class_ $class, Property $property): void
    {
        $propertyProperty = $property->props[0];

        $propertyName = $this->nodeNameResolver->getName($property);
        $constantName = $this->constantNaming->createFromProperty($propertyProperty);

        $this->callableNodeTraverser->traverseNodesWithCallable($class, function (Node $node) use (
            $propertyName,
            $constantName
        ): ?ClassConstFetch {
            if (! $this->propertyFetchAnalyzer->isLocalPropertyFetch($node)) {
                return null;
            }

            /** @var PropertyFetch|StaticPropertyFetch $node */
            if (! $this->nodeNameResolver->isName($node->name, $propertyName)) {
                return null;
            }

            // replace with constant fetch
            // replace with constant fetch
            $name = $this->createSelf($node);
            return new ClassConstFetch($name, $constantName);
        });
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $propertyFetch
     */
    private function createSelf($propertyFetch): Name
    {
        $name = new Name('self');
        $name->setAttribute(AttributeKey::CLASS_NAME, $propertyFetch->getAttribute(AttributeKey::CLASS_NAME));

        return $name;
    }
}

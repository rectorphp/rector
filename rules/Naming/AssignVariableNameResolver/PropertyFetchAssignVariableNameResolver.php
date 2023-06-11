<?php

declare (strict_types=1);
namespace Rector\Naming\AssignVariableNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Naming\Contract\AssignVariableNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @implements AssignVariableNameResolverInterface<PropertyFetch>
 */
final class PropertyFetchAssignVariableNameResolver implements AssignVariableNameResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function match(Node $node) : bool
    {
        return $node instanceof PropertyFetch;
    }
    /**
     * @param PropertyFetch $node
     */
    public function resolve(Node $node) : string
    {
        $varName = $this->nodeNameResolver->getName($node->var);
        if (!\is_string($varName)) {
            throw new NotImplementedYetException();
        }
        $propertyName = $this->nodeNameResolver->getName($node->name);
        if (!\is_string($propertyName)) {
            throw new NotImplementedYetException();
        }
        if ($varName === 'this') {
            return $propertyName;
        }
        return $varName . \ucfirst($propertyName);
    }
}

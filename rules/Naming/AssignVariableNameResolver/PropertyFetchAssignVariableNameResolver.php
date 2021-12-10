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
final class PropertyFetchAssignVariableNameResolver implements \Rector\Naming\Contract\AssignVariableNameResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function match(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Expr\PropertyFetch;
    }
    /**
     * @param PropertyFetch $node
     */
    public function resolve(\PhpParser\Node $node) : string
    {
        $varName = $this->nodeNameResolver->getName($node->var);
        if (!\is_string($varName)) {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        $propertyName = $this->nodeNameResolver->getName($node->name);
        if (!\is_string($propertyName)) {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        if ($varName === 'this') {
            return $propertyName;
        }
        return $varName . \ucfirst($propertyName);
    }
}

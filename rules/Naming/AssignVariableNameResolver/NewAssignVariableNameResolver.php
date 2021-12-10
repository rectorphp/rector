<?php

declare (strict_types=1);
namespace Rector\Naming\AssignVariableNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Naming\Contract\AssignVariableNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @implements AssignVariableNameResolverInterface<New_>
 */
final class NewAssignVariableNameResolver implements \Rector\Naming\Contract\AssignVariableNameResolverInterface
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
        return $node instanceof \PhpParser\Node\Expr\New_;
    }
    /**
     * @param New_ $node
     */
    public function resolve(\PhpParser\Node $node) : string
    {
        $className = $this->nodeNameResolver->getName($node->class);
        if ($className === null) {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        return $this->nodeNameResolver->getShortName($className);
    }
}

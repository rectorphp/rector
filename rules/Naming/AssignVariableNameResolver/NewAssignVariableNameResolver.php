<?php

declare (strict_types=1);
namespace Rector\Naming\AssignVariableNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Exception\NotImplementedYetException;
use Rector\Naming\Contract\AssignVariableNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @implements AssignVariableNameResolverInterface<New_>
 */
final class NewAssignVariableNameResolver implements AssignVariableNameResolverInterface
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
        return $node instanceof New_;
    }
    /**
     * @param New_ $node
     */
    public function resolve(Node $node) : string
    {
        $className = $this->nodeNameResolver->getName($node->class);
        if ($className === null) {
            throw new NotImplementedYetException();
        }
        return $this->nodeNameResolver->getShortName($className);
    }
}

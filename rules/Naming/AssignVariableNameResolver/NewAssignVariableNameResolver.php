<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\AssignVariableNameResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\Naming\Contract\AssignVariableNameResolverInterface;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
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

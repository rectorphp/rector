<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Variable>
 */
final class VariableNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Variable::class;
    }
    /**
     * @param Variable $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->name instanceof Expr) {
            return null;
        }
        return $node->name;
    }
}

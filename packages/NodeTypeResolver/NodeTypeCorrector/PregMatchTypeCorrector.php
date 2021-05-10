<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeCorrector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PregMatchTypeCorrector
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private ParentScopeFinder $parentScopeFinder,
        private NodeComparator $nodeComparator
    ) {
    }

    /**
     * Special case for "preg_match(), preg_match_all()" - with 3rd argument
     * @see https://github.com/rectorphp/rector/issues/786
     */
    public function correct(Node $node, Type $originalType): Type
    {
        if (! $node instanceof Variable) {
            return $originalType;
        }

        if ($originalType instanceof ArrayType) {
            return $originalType;
        }

        $variableUsages = $this->getVariableUsages($node);
        foreach ($variableUsages as $variableUsage) {
            $possiblyArg = $variableUsage->getAttribute(AttributeKey::PARENT_NODE);
            if (! $possiblyArg instanceof Arg) {
                continue;
            }

            $funcCallNode = $possiblyArg->getAttribute(AttributeKey::PARENT_NODE);

            if (! $funcCallNode instanceof FuncCall) {
                continue;
            }

            if (! $this->nodeNameResolver->isNames($funcCallNode, ['preg_match', 'preg_match_all'])) {
                continue;
            }

            if (! isset($funcCallNode->args[2])) {
                continue;
            }

            // are the same variables
            if (! $this->nodeComparator->areNodesEqual($funcCallNode->args[2]->value, $node)) {
                continue;
            }

            return new ArrayType(new MixedType(), new MixedType());
        }

        return $originalType;
    }

    /**
     * @return Node[]
     */
    private function getVariableUsages(Variable $variable): array
    {
        $scope = $this->parentScopeFinder->find($variable);
        if ($scope === null) {
            return [];
        }

        return $this->betterNodeFinder->find((array) $scope->stmts, function (Node $node) use ($variable): bool {
            if (! $node instanceof Variable) {
                return false;
            }
            return $node->name === $variable->name;
        });
    }
}

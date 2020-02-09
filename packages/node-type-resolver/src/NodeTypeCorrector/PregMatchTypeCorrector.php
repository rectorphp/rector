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
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PregMatchTypeCorrector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * Special case for "preg_match(), preg_match_all()" - with 3rd argument
     * @covers https://github.com/rectorphp/rector/issues/786
     */
    public function correct(Node $node, Type $originalType): Type
    {
        if (! $node instanceof Variable) {
            return $originalType;
        }

        if ($originalType instanceof ArrayType) {
            return $originalType;
        }

        foreach ($this->getVariableUsages($node) as $usage) {
            $possiblyArg = $usage->getAttribute(AttributeKey::PARENT_NODE);
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
            if (! $this->betterStandardPrinter->areNodesEqual($funcCallNode->args[2]->value, $node)) {
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
        $scope = $this->getScopeNode($variable);

        if ($scope === null) {
            return [];
        }

        return $this->betterNodeFinder->find((array) $scope->stmts, function (Node $node) use ($variable): bool {
            return $node instanceof Variable && $node->name === $variable->name;
        });
    }

    private function getScopeNode(Node $node): ?Node
    {
        return $node->getAttribute(AttributeKey::METHOD_NODE)
            ?? $node->getAttribute(AttributeKey::FUNCTION_NODE)
            ?? $node->getAttribute(AttributeKey::NAMESPACE_NODE);
    }
}

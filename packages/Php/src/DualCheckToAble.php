<?php declare(strict_types=1);

namespace Rector\Php;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class DualCheckToAble
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BinaryOpMaintainer
     */
    private $binaryOpMaintainer;

    public function __construct(NameResolver $nameResolver, BinaryOpMaintainer $binaryOpMaintainer)
    {
        $this->nameResolver = $nameResolver;
        $this->binaryOpMaintainer = $binaryOpMaintainer;
    }

    public function processBooleanOr(BooleanOr $booleanOrNode, string $type, string $newMethodName): ?FuncCall
    {
        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $booleanOrNode,
            function (Node $node) {
                return $node instanceof Instanceof_;
            },
            function (Node $node) {
                return $node instanceof FuncCall;
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        [$instanceOfNode, $funcCallNode] = $matchedNodes;

        /** @var Instanceof_ $instanceOfNode */
        if ((string) $instanceOfNode->class !== $type) {
            return null;
        }

        /** @var FuncCall $funcCallNode */
        if ($this->nameResolver->resolve($funcCallNode) !== 'is_array') {
            return null;
        }

        // both use same var
        if (! $funcCallNode->args[0]->value instanceof Variable) {
            return null;
        }

        /** @var Variable $firstVarNode */
        $firstVarNode = $funcCallNode->args[0]->value;

        if (! $instanceOfNode->expr instanceof Variable) {
            return null;
        }

        /** @var Variable $secondVarNode */
        $secondVarNode = $instanceOfNode->expr;

        // are they same variables
        if ($firstVarNode->name !== $secondVarNode->name) {
            return null;
        }

        $funcCallNode = new FuncCall(new Name($newMethodName));
        $funcCallNode->args[0] = new Arg($firstVarNode);

        return $funcCallNode;
    }
}

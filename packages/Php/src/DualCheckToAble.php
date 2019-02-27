<?php declare(strict_types=1);

namespace Rector\Php;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class DualCheckToAble
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(NameResolver $nameResolver, BinaryOpManipulator $binaryOpManipulator)
    {
        $this->nameResolver = $nameResolver;
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function processBooleanOr(BooleanOr $booleanOr, string $type, string $newMethodName): ?FuncCall
    {
        $matchedNodes = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $booleanOr,
            Instanceof_::class,
            FuncCall::class
        );

        if ($matchedNodes === null) {
            return null;
        }

        /** @var Instanceof_ $instanceOfNode */
        /** @var FuncCall $funcCallNode */
        [$instanceOfNode, $funcCallNode] = $matchedNodes;

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

        return new FuncCall(new Name($newMethodName), [new Arg($firstVarNode)]);
    }
}

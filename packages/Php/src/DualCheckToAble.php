<?php declare(strict_types=1);

namespace Rector\Php;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\NodeAnalyzer\NameResolver;

final class DualCheckToAble
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function processBooleanOr(BooleanOr $booleanOrNode, string $type, string $newMethodName): ?FuncCall
    {
        $split = $this->splitToInstanceOfAndFuncCall($booleanOrNode);
        if ($split === null) {
            return null;
        }

        [$instanceOfNode, $funcCallNode] = $split;

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

    /**
     * @return Instanceof_[]|FuncCall[]
     */
    private function splitToInstanceOfAndFuncCall(BooleanOr $booleanOrNode): ?array
    {
        if ($booleanOrNode->left instanceof Instanceof_ && $booleanOrNode->right instanceof FuncCall) {
            return [$booleanOrNode->left, $booleanOrNode->right];
        }

        if ($booleanOrNode->right instanceof Instanceof_ && $booleanOrNode->left instanceof FuncCall) {
            return [$booleanOrNode->right, $booleanOrNode->left];
        }

        return null;
    }
}

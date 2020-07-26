<?php

declare(strict_types=1);

namespace Rector\DeadCode\SideEffect;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use PHPStan\Type\ConstantType;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SideEffectNodeDetector
{
    /**
     * @var string[]
     */
    private const SIDE_EFFECT_NODE_TYPES = [Encapsed::class, New_::class, Concat::class, PropertyFetch::class];

    /**
     * @var PureFunctionDetector
     */
    private $pureFunctionDetector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver, PureFunctionDetector $pureFunctionDetector)
    {
        $this->pureFunctionDetector = $pureFunctionDetector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function detect(Expr $expr): bool
    {
        if ($expr instanceof Assign) {
            return true;
        }

        $exprStaticType = $this->nodeTypeResolver->resolve($expr);
        if ($exprStaticType instanceof ConstantType) {
            return false;
        }

        foreach (self::SIDE_EFFECT_NODE_TYPES as $sideEffectNodeType) {
            if (is_a($expr, $sideEffectNodeType, true)) {
                return false;
            }
        }

        if ($expr instanceof FuncCall) {
            return ! $this->pureFunctionDetector->detect($expr);
        }

        if ($expr instanceof Variable || $expr instanceof ArrayDimFetch) {
            $variable = $this->resolveVariable($expr);
            // variables don't have side effects
            return ! $variable instanceof Variable;
        }

        return true;
    }

    private function resolveVariable(Expr $expr): ?Variable
    {
        while ($expr instanceof ArrayDimFetch) {
            $expr = $expr->var;
        }

        if (! $expr instanceof Variable) {
            return null;
        }

        return $expr;
    }
}

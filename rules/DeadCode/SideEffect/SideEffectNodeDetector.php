<?php

declare(strict_types=1);

namespace Rector\DeadCode\SideEffect;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Encapsed;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SideEffectNodeDetector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const SIDE_EFFECT_NODE_TYPES = [Encapsed::class, New_::class, Concat::class, PropertyFetch::class];

    /**
     * @var array<class-string<Expr>>
     */
    private const CALL_EXPR_SIDE_EFFECT_NODE_TYPES = [
        MethodCall::class,
        New_::class,
        NullsafeMethodCall::class,
        StaticCall::class,
    ];

    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private PureFunctionDetector $pureFunctionDetector
    ) {
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

    public function detectCallExpr(Node $node): bool
    {
        if (! $node instanceof Expr) {
            return false;
        }

        if ($node instanceof StaticCall && $this->isClassCallerThrowable($node)) {
            return false;
        }

        $exprClass = $node::class;
        if (in_array($exprClass, self::CALL_EXPR_SIDE_EFFECT_NODE_TYPES, true)) {
            return true;
        }

        if ($node instanceof FuncCall) {
            return ! $this->pureFunctionDetector->detect($node);
        }

        return false;
    }

    private function isClassCallerThrowable(StaticCall $staticCall): bool
    {
        $class = $staticCall->class;
        if (! $class instanceof Name) {
            return false;
        }

        $throwableType = new ObjectType('Throwable');
        $type = new ObjectType($class->toString());

        return $throwableType->isSuperTypeOf($type)
            ->yes();
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

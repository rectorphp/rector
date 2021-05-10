<?php

declare (strict_types=1);
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
     * @var array<class-string<Expr>>
     */
    private const SIDE_EFFECT_NODE_TYPES = [\PhpParser\Node\Scalar\Encapsed::class, \PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\BinaryOp\Concat::class, \PhpParser\Node\Expr\PropertyFetch::class];
    /**
     * @var PureFunctionDetector
     */
    private $pureFunctionDetector;
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector)
    {
        $this->pureFunctionDetector = $pureFunctionDetector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function detect(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        $exprStaticType = $this->nodeTypeResolver->resolve($expr);
        if ($exprStaticType instanceof \PHPStan\Type\ConstantType) {
            return \false;
        }
        foreach (self::SIDE_EFFECT_NODE_TYPES as $sideEffectNodeType) {
            if (\is_a($expr, $sideEffectNodeType, \true)) {
                return \false;
            }
        }
        if ($expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return !$this->pureFunctionDetector->detect($expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable || $expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $variable = $this->resolveVariable($expr);
            // variables don't have side effects
            return !$variable instanceof \PhpParser\Node\Expr\Variable;
        }
        return \true;
    }
    private function resolveVariable(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\Variable
    {
        while ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $expr = $expr->var;
        }
        if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        return $expr;
    }
}

<?php

declare (strict_types=1);
namespace Rector\DeadCode\SideEffect;

use RectorPrefix20211231\Nette\Utils\Strings;
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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Encapsed;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class SideEffectNodeDetector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const SIDE_EFFECT_NODE_TYPES = [\PhpParser\Node\Scalar\Encapsed::class, \PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\BinaryOp\Concat::class, \PhpParser\Node\Expr\PropertyFetch::class];
    /**
     * @var array<class-string<Expr>>
     */
    private const CALL_EXPR_SIDE_EFFECT_NODE_TYPES = [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\New_::class, \PhpParser\Node\Expr\NullsafeMethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\PureFunctionDetector
     */
    private $pureFunctionDetector;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->pureFunctionDetector = $pureFunctionDetector;
    }
    public function detect(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        $exprStaticType = $this->nodeTypeResolver->getType($expr);
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
    public function detectCallExpr(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall && $this->isClassCallerThrowable($node)) {
            return \false;
        }
        if ($node instanceof \PhpParser\Node\Expr\New_ && $this->isPhpParser($node)) {
            return \false;
        }
        $exprClass = \get_class($node);
        if (\in_array($exprClass, self::CALL_EXPR_SIDE_EFFECT_NODE_TYPES, \true)) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
            return !$this->pureFunctionDetector->detect($node);
        }
        return \false;
    }
    private function isPhpParser(\PhpParser\Node\Expr\New_ $new) : bool
    {
        if (!$new->class instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        $className = $new->class->toString();
        $namespace = \RectorPrefix20211231\Nette\Utils\Strings::before($className, '\\', 1);
        return $namespace === 'PhpParser';
    }
    private function isClassCallerThrowable(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        $class = $staticCall->class;
        if (!$class instanceof \PhpParser\Node\Name) {
            return \false;
        }
        $throwableType = new \PHPStan\Type\ObjectType('Throwable');
        $type = new \PHPStan\Type\ObjectType($class->toString());
        return $throwableType->isSuperTypeOf($type)->yes();
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\Variable $expr
     */
    private function resolveVariable($expr) : ?\PhpParser\Node\Expr\Variable
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

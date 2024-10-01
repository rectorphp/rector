<?php

declare (strict_types=1);
namespace Rector\DeadCode\SideEffect;

use RectorPrefix202410\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\BetterNodeFinder;
final class SideEffectNodeDetector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\PureFunctionDetector
     */
    private $pureFunctionDetector;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var array<class-string<Expr>>
     */
    private const CALL_EXPR_SIDE_EFFECT_NODE_TYPES = [MethodCall::class, New_::class, NullsafeMethodCall::class, StaticCall::class];
    public function __construct(\Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector, BetterNodeFinder $betterNodeFinder)
    {
        $this->pureFunctionDetector = $pureFunctionDetector;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function detect(Expr $expr, Scope $scope) : bool
    {
        if ($expr instanceof Assign) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst($expr, function (Node $subNode) use($scope) : bool {
            return $this->detectCallExpr($subNode, $scope);
        });
    }
    public function detectCallExpr(Node $node, Scope $scope) : bool
    {
        if (!$node instanceof Expr) {
            return \false;
        }
        if ($node instanceof StaticCall && $this->isClassCallerThrowable($node)) {
            return \false;
        }
        if ($node instanceof New_ && $this->isPhpParser($node)) {
            return \false;
        }
        $exprClass = \get_class($node);
        if (\in_array($exprClass, self::CALL_EXPR_SIDE_EFFECT_NODE_TYPES, \true)) {
            return \true;
        }
        if ($node instanceof FuncCall) {
            return !$this->pureFunctionDetector->detect($node, $scope);
        }
        if ($node instanceof Variable || $node instanceof ArrayDimFetch) {
            $variable = $this->resolveVariable($node);
            // variables don't have side effects
            return !$variable instanceof Variable;
        }
        return \false;
    }
    private function isPhpParser(New_ $new) : bool
    {
        if (!$new->class instanceof FullyQualified) {
            return \false;
        }
        $className = $new->class->toString();
        $namespace = Strings::before($className, '\\', 1);
        return $namespace === 'PhpParser';
    }
    private function isClassCallerThrowable(StaticCall $staticCall) : bool
    {
        $class = $staticCall->class;
        if (!$class instanceof Name) {
            return \false;
        }
        $throwableType = new ObjectType('Throwable');
        $type = new ObjectType($class->toString());
        return $throwableType->isSuperTypeOf($type)->yes();
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\Variable $expr
     */
    private function resolveVariable($expr) : ?Variable
    {
        while ($expr instanceof ArrayDimFetch) {
            $expr = $expr->var;
        }
        if (!$expr instanceof Variable) {
            return null;
        }
        return $expr;
    }
}

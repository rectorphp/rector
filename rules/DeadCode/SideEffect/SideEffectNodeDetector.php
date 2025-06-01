<?php

declare (strict_types=1);
namespace Rector\DeadCode\SideEffect;

use RectorPrefix202506\Nette\Utils\Strings;
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
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class SideEffectNodeDetector
{
    /**
     * @readonly
     */
    private \Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @var array<class-string<Expr>>
     */
    private const CALL_EXPR_SIDE_EFFECT_NODE_TYPES = [MethodCall::class, New_::class, NullsafeMethodCall::class, StaticCall::class];
    public function __construct(\Rector\DeadCode\SideEffect\PureFunctionDetector $pureFunctionDetector, BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->pureFunctionDetector = $pureFunctionDetector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(Expr $expr) : bool
    {
        if ($expr instanceof Assign) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst($expr, fn(Node $subNode): bool => $this->detectCallExpr($subNode));
    }
    public function detectCallExpr(Node $node) : bool
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
        if (($node instanceof MethodCall || $node instanceof StaticCall) && $this->isTestMock($node)) {
            return \false;
        }
        $exprClass = \get_class($node);
        if (\in_array($exprClass, self::CALL_EXPR_SIDE_EFFECT_NODE_TYPES, \true)) {
            return \true;
        }
        if ($node instanceof FuncCall) {
            return !$this->pureFunctionDetector->detect($node);
        }
        if ($node instanceof Variable || $node instanceof ArrayDimFetch) {
            $variable = $this->resolveVariable($node);
            // variables don't have side effects
            return !$variable instanceof Variable;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function isTestMock($node) : bool
    {
        $objectType = new ObjectType('PHPUnit\\Framework\\TestCase');
        $nodeCaller = $node instanceof MethodCall ? $node->var : $node->class;
        if (!$this->nodeTypeResolver->isObjectType($nodeCaller, $objectType)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node->name, 'createMock');
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

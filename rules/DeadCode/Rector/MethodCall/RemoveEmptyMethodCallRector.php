<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends AbstractRector
{
    public function __construct(
        private AstResolver $reflectionAstResolver,
        private CallAnalyzer $callAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove empty method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
$some->callThis();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = $this->getScope($node);

        if (! $scope instanceof Scope) {
            return null;
        }

        $type = $scope->getType($node->var);
        if (! $type instanceof TypeWithClassName) {
            return null;
        }

        $classLike = $this->reflectionAstResolver->resolveClassFromObjectType($type);
        if ($classLike === null) {
            return null;
        }

        if ($this->shouldSkipClassMethod($classLike, $node, $type)) {
            return null;
        }

        // if->cond cannot removed, it has to be replaced with false, see https://3v4l.org/U9S9i
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof If_ && $parent->cond === $node) {
            return $this->nodeFactory->createFalse();
        }

        if ($parent instanceof Assign) {
            return $this->nodeFactory->createFalse();
        }

        if ($parent instanceof ArrowFunction && $this->nodeComparator->areNodesEqual($parent->expr, $node)) {
            return $this->processArrowFunction($parent, $node);
        }

        $this->removeNode($node);

        return $node;
    }

    private function getScope(MethodCall $methodCall): ?Scope
    {
        if ($this->callAnalyzer->isObjectCall($methodCall->var)) {
            return null;
        }

        $parentArg = $this->betterNodeFinder->findParentType($methodCall, Arg::class);
        if ($parentArg instanceof Arg) {
            return null;
        }

        $scope = $methodCall->var->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        return $scope;
    }

    private function shouldSkipClassMethod(
        Class_ | Trait_ | Interface_ $classLike,
        MethodCall $methodCall,
        TypeWithClassName $typeWithClassName
    ): bool {
        if (! $classLike instanceof Class_) {
            return true;
        }

        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return true;
        }

        $classMethod = $classLike->getMethod($methodName);
        if (! $classMethod instanceof ClassMethod) {
            return true;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        if ((array) $classMethod->stmts !== []) {
            return true;
        }

        $class = $methodCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return false;
        }

        return $typeWithClassName instanceof ThisType && ! $class->isFinal() && ! $classMethod->isPrivate();
    }

    private function processArrowFunction(ArrowFunction $arrowFunction, MethodCall $methodCall): MethodCall | ConstFetch
    {
        $parentOfParent = $arrowFunction->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentOfParent instanceof Expression) {
            $this->removeNode($arrowFunction);
            return $methodCall;
        }

        return $this->nodeFactory->createFalse();
    }
}

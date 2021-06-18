<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ClassReflectionToAstResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends AbstractRector
{
    public function __construct(
        private ClassReflectionToAstResolver $classReflectionToAstResolver
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
        $scope = $node->var->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $type = $scope->getType($node->var);
        if ($type instanceof ThisType) {
            $type = $type->getStaticObjectType();
        }

        if (! $type instanceof ObjectType) {
            return null;
        }

        $class = $this->classReflectionToAstResolver->getClassFromObjectType($type);
        if ($this->shouldSkipClassMethod($class, $node)) {
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

    private function shouldSkipClassMethod(?Class_ $class, MethodCall $methodCall): bool
    {
        if (! $class instanceof Class_) {
            return true;
        }

        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return true;
        }

        $classMethod = $class->getMethod($methodName);
        if (! $classMethod instanceof ClassMethod) {
            return true;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        return (array) $classMethod->stmts !== [];
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

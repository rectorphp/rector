<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractRector
{
    public function __construct(
        private ArrayCallableMethodMatcher $arrayCallableMethodMatcher
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused private method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        $classMethodName = $this->nodeNameResolver->getName($node);

        // 1. direct normal calls
        if ($this->isClassMethodCalledInLocalMethodCall($class, $classMethodName)) {
            return null;
        }

        // 2. direct static calls
        if ($this->isClassMethodUsedInLocalStaticCall($class, $classMethodName)) {
            return null;
        }

        // 3. magic array calls!
        if ($this->isClassMethodCalledInLocalArrayCall($class, $node)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return true;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        // unreliable to detect trait, interface doesn't make sense
        if ($classReflection->isTrait()) {
            return true;
        }

        if ($classReflection->isInterface()) {
            return true;
        }

        if ($classReflection->isAnonymous()) {
            return true;
        }

        // skips interfaces by default too
        if (! $classMethod->isPrivate()) {
            return true;
        }

        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return true;
        }

        return $classReflection->hasMethod(MethodName::CALL);
    }

    private function isClassMethodUsedInLocalStaticCall(Class_ $class, string $classMethodName): bool
    {
        $className = $this->getName($class);

        /** @var StaticCall[] $staticCalls */
        $staticCalls = $this->betterNodeFinder->findInstanceOf($class, StaticCall::class);
        foreach ($staticCalls as $staticCall) {
            $callerType = $this->nodeTypeResolver->resolve($staticCall->class);
            if (! $callerType instanceof TypeWithClassName) {
                continue;
            }

            if ($callerType->getClassName() !== $className) {
                continue;
            }

            if ($this->isName($staticCall->name, $classMethodName)) {
                return true;
            }
        }

        return false;
    }

    private function isClassMethodCalledInLocalMethodCall(Class_ $class, string $classMethodName): bool
    {
        $className = $this->getName($class);

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($class, MethodCall::class);
        foreach ($methodCalls as $methodCall) {
            $callerType = $this->nodeTypeResolver->resolve($methodCall->var);
            if (! $callerType instanceof TypeWithClassName) {
                continue;
            }

            if ($callerType->getClassName() !== $className) {
                continue;
            }

            // the method is used
            if ($this->isName($methodCall->name, $classMethodName)) {
                return true;
            }
        }

        return false;
    }

    private function isClassMethodCalledInLocalArrayCall(Class_ $class, ClassMethod $classMethod): bool
    {
        /** @var Array_[] $arrays */
        $arrays = $this->betterNodeFinder->findInstanceOf($class, Array_::class);

        foreach ($arrays as $array) {
            $arrayCallable = $this->arrayCallableMethodMatcher->match($array);
            if (! $arrayCallable instanceof ArrayCallable) {
                continue;
            }

            // is current class method?
            if (! $this->isName($class, $arrayCallable->getClass())) {
                continue;
            }

            // the method is used
            if ($this->nodeNameResolver->isName($classMethod->name, $arrayCallable->getMethod())) {
                return true;
            }
        }

        return false;
    }
}

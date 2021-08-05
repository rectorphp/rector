<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\NodeAnalyzer\CallCollectionAnalyzer;
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
        private ArrayCallableMethodMatcher $arrayCallableMethodMatcher,
        private CallCollectionAnalyzer $callCollectionAnalyzer
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
        return $this->callCollectionAnalyzer->isExists($staticCalls, $classMethodName, $className);
    }

    private function isClassMethodCalledInLocalMethodCall(Class_ $class, string $classMethodName): bool
    {
        $className = $this->getName($class);

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($class, MethodCall::class);
        return $this->callCollectionAnalyzer->isExists($methodCalls, $classMethodName, $className);
    }

    private function isInArrayMap(Class_ $class, Array_ $array): bool
    {
        $parentFuncCall = $this->betterNodeFinder->findParentType($array, FuncCall::class);
        if (! $parentFuncCall instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($parentFuncCall->name, 'array_map')) {
            return false;
        }

        if (count($array->items) !== 2) {
            return false;
        }

        if (! $array->items[1] instanceof ArrayItem) {
            return false;
        }

        $value = $this->valueResolver->getValue($array->items[1]->value);

        if (! is_string($value)) {
            return false;
        }

        return $class->getMethod($value) instanceof ClassMethod;
    }

    private function isClassMethodCalledInLocalArrayCall(Class_ $class, ClassMethod $classMethod): bool
    {
        /** @var Array_[] $arrays */
        $arrays = $this->betterNodeFinder->findInstanceOf($class, Array_::class);

        foreach ($arrays as $array) {
            if ($this->isInArrayMap($class, $array)) {
                return true;
            }

            $arrayCallable = $this->arrayCallableMethodMatcher->match($array);
            if ($this->shouldSkipArrayCallable($class, $arrayCallable)) {
                continue;
            }

            // the method is used
            /** @var ArrayCallable $arrayCallable */
            if ($this->nodeNameResolver->isName($classMethod->name, $arrayCallable->getMethod())) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipArrayCallable(Class_ $class, ?ArrayCallable $arrayCallable): bool
    {
        if (! $arrayCallable instanceof ArrayCallable) {
            return true;
        }

        // is current class method?
        return ! $this->isName($class, $arrayCallable->getClass());
    }
}

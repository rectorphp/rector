<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Array_\ArrayThisCallToThisMethodCallRector\ArrayThisCallToThisMethodCallRectorTest
 */
final class ArrayThisCallToThisMethodCallRector extends AbstractRector
{
    /**
     * @var ArrayCallableMethodReferenceAnalyzer
     */
    private $arrayCallableMethodReferenceAnalyzer;

    public function __construct(ArrayCallableMethodReferenceAnalyzer $arrayCallableMethodReferenceAnalyzer)
    {
        $this->arrayCallableMethodReferenceAnalyzer = $arrayCallableMethodReferenceAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change `[$this, someMethod]` without any args to $this->someMethod()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [$this, 'giveMeMore'];
    }

    public function giveMeMore()
    {
        return 'more';
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = $this->giveMeMore();
    }

    public function giveMeMore()
    {
        return 'more';
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Array_::class];
    }

    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $arrayCallable = $this->arrayCallableMethodReferenceAnalyzer->match($node);
        if (! $arrayCallable instanceof ArrayCallable) {
            return null;
        }
        if ($this->isAssignedToNetteMagicOnProperty($node)) {
            return null;
        }
        if ($this->isInsideProperty($node)) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // skip if part of method
        if ($parentNode instanceof Arg) {
            return null;
        }

        if (! $arrayCallable->isExistingMethod()) {
            return null;
        }

        $reflectionMethod = $arrayCallable->getReflectionMethod();

        $this->privatizeClassMethod($reflectionMethod);

        if ($reflectionMethod->getNumberOfParameters() > 0) {
            $classMethod = $this->nodeRepository->findClassMethod(
                $arrayCallable->getClass(),
                $arrayCallable->getMethod()
            );
            if ($classMethod !== null) {
                return $this->nodeFactory->createClosureFromClassMethod($classMethod);
            }

            return null;
        }

        return new MethodCall(new Variable('this'), $arrayCallable->getMethod());
    }

    private function isAssignedToNetteMagicOnProperty(Array_ $array): bool
    {
        $parent = $array->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Assign) {
            return false;
        }

        if (! $parent->var instanceof ArrayDimFetch) {
            return false;
        }

        if (! $parent->var->var instanceof PropertyFetch) {
            return false;
        }

        /** @var PropertyFetch $propertyFetch */
        $propertyFetch = $parent->var->var;
        return $this->isName($propertyFetch->name, 'on*');
    }

    private function isInsideProperty(Array_ $array): bool
    {
        $parentProperty = $this->betterNodeFinder->findParentType($array, Property::class);

        return $parentProperty !== null;
    }

    private function privatizeClassMethod(ReflectionMethod $reflectionMethod): void
    {
        $classMethod = $this->nodeRepository->findClassMethodByMethodReflection($reflectionMethod);
        if (! $classMethod instanceof ClassMethod) {
            return;
        }

        if ($classMethod->isPrivate()) {
            return;
        }

        $this->visibilityManipulator->makePrivate($classMethod);
    }
}

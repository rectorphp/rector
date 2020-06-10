<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableClassMethodReferenceAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Array_\ArrayThisCallToThisMethodCallRector\ArrayThisCallToThisMethodCallRectorTest
 */
final class ArrayThisCallToThisMethodCallRector extends AbstractRector
{
    /**
     * @var ArrayCallableClassMethodReferenceAnalyzer
     */
    private $arrayCallableClassMethodReferenceAnalyzer;

    public function __construct(ArrayCallableClassMethodReferenceAnalyzer $arrayCallableClassMethodReferenceAnalyzer)
    {
        $this->arrayCallableClassMethodReferenceAnalyzer = $arrayCallableClassMethodReferenceAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change [$this, someMethod] without any args to $this->someMethod()', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP

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
        $arrayCallable = $this->arrayCallableClassMethodReferenceAnalyzer->match($node);
        if ($arrayCallable === null) {
            return null;
        }

        if ($this->isAssignedToNetteMagicOnProperty($node)) {
            return null;
        }

        [$class, $method] = $arrayCallable;

        $methodReflection = new ReflectionMethod($class, $method);
        if ($methodReflection->getNumberOfParameters() > 0) {
            return null;
        }

        return new MethodCall(new Variable('this'), $method);
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
}

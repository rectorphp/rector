<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\AbstractArgumentReplacerRecipe;
use Rector\NodeAnalyzer\ClassMethodAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;

abstract class AbstractArgumentRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var ClassMethodAnalyzer
     */
    private $classMethodAnalyzer;

    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

    protected function isNodeToRecipeMatch(Node $node, AbstractArgumentReplacerRecipe $argumentReplacerRecipe): bool
    {
        $type = $argumentReplacerRecipe->getClass();
        $method = $argumentReplacerRecipe->getMethod();

        if ($this->methodCallAnalyzer->isTypeAndMethods($node, $type, [$method])) {
            return true;
        }

        if ($this->staticMethodCallAnalyzer->isTypeAndMethods($node, $type, [$method])) {
            return true;
        }

        return $this->classMethodAnalyzer->isTypeAndMethods($node, $type, [$method]);
    }

    /**
     * @return Arg[]|Param[]
     */
    protected function getNodeArgumentsOrParameters(Node $node): array
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            return $node->args;
        }

        if ($node instanceof ClassMethod) {
            return $node->params;
        }
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param mixed[] $argumentsOrParameters
     */
    protected function setNodeArgumentsOrParameters(Node $node, array $argumentsOrParameters): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $node->args = $argumentsOrParameters;
        }

        if ($node instanceof ClassMethod) {
            $node->params = $argumentsOrParameters;
        }
    }

    protected function isValidInstance(Node $node): bool
    {
        return $node instanceof ClassMethod || $node instanceof MethodCall || $node instanceof StaticCall;
    }

    /**
     * @param MethodCallAnalyzer $methodCallAnalyzer
     * @required
     */
    final public function setMethodCallAnalyzer(MethodCallAnalyzer $methodCallAnalyzer): void
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    /**
     * @param ClassMethodAnalyzer $classMethodAnalyzer
     * @required
     */
    final public function setClassMethodAnalyzer(ClassMethodAnalyzer $classMethodAnalyzer): void
    {
        $this->classMethodAnalyzer = $classMethodAnalyzer;
    }

    /**
     * @param StaticMethodCallAnalyzer $staticMethodCallAnalyzer
     * @required
     */
    final public function setStaticMethodCallAnalyzer(StaticMethodCallAnalyzer $staticMethodCallAnalyzer): void
    {
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
    }
}

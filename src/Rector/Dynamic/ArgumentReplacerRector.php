<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\BuilderHelpers;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeAnalyzer\ClassMethodAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Rector\Dynamic\Configuration\ArgumentReplacerRecipe;

final class ArgumentReplacerRector extends AbstractRector
{
    /**
     * @var ArgumentReplacerRecipe[]
     */
    private $argumentReplacerRecipes = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var ArgumentReplacerRecipe[]
     */
    private $activeArgumentReplacerItemRecipes = [];

    /**
     * @var ClassMethodAnalyzer
     */
    private $classMethodAnalyzer;

    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        MethodCallAnalyzer $methodCallAnalyzer,
        ClassMethodAnalyzer $classMethodAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        ConstExprEvaluator $constExprEvaluator
    ) {
        $this->loadArgumentReplacerItemRecipes($argumentChangesByMethodAndType);
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->classMethodAnalyzer = $classMethodAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
        $this->constExprEvaluator = $constExprEvaluator;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeArgumentReplacerItemRecipes = $this->matchArgumentChanges($node);

        return (bool) $this->activeArgumentReplacerItemRecipes;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $argumentsOrParameters = $this->getNodeArgumentsOrParameters($node);

        foreach ($this->activeArgumentReplacerItemRecipes as $argumentReplacerItemRecipe) {
            $type = $argumentReplacerItemRecipe->getType();
            $position = $argumentReplacerItemRecipe->getPosition();

            if ($type === ArgumentReplacerRecipe::TYPE_REMOVED) {
                unset($argumentsOrParameters[$position]);
                continue;
            }

            if ($type === ArgumentReplacerRecipe::TYPE_CHANGED) {
                $argumentsOrParameters[$position] = BuilderHelpers::normalizeValue(
                    $argumentReplacerItemRecipe->getDefaultValue()
                );
                continue;
            }

            if ($type === ArgumentReplacerRecipe::TYPE_REPLACED_DEFAULT_VALUE) {
                /** @var Arg $argumentOrParameter */
                $argumentOrParameter = $argumentsOrParameters[$position];
                $resolvedValue = $this->constExprEvaluator->evaluateDirectly($argumentOrParameter->value);

                $replaceMap = $argumentReplacerItemRecipe->getReplaceMap();
                foreach ($replaceMap as $oldValue => $newValue) {
                    if ($resolvedValue === $oldValue) {
                        $argumentsOrParameters[$position] = BuilderHelpers::normalizeValue($newValue);
                    }
                }
            }
        }

        $this->setNodeArgumentsOrParameters($node, $argumentsOrParameters);

        return $node;
    }

    /**
     * @return ArgumentReplacerRecipe[]
     */
    private function matchArgumentChanges(Node $node): array
    {
        if (! $node instanceof ClassMethod && ! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return [];
        }

        $argumentReplacerItemRecipes = [];

        foreach ($this->argumentReplacerRecipes as $argumentReplacerRecipe) {
            if ($this->isRecipeMatch($node, $argumentReplacerRecipe)) {
                $argumentReplacerItemRecipes[] = $argumentReplacerRecipe;
            }
        }

        return $argumentReplacerItemRecipes;
    }

    /**
     * @return Arg[]|Param[]
     */
    private function getNodeArgumentsOrParameters(Node $node): array
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
    private function setNodeArgumentsOrParameters(Node $node, array $argumentsOrParameters): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $node->args = $argumentsOrParameters;
        }

        if ($node instanceof ClassMethod) {
            $node->params = $argumentsOrParameters;
        }
    }

    private function isRecipeMatch(Node $node, ArgumentReplacerRecipe $argumentReplacerRecipe): bool
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
     * @param mixed[] $configurationArrays
     */
    private function loadArgumentReplacerItemRecipes(array $configurationArrays): void
    {
        foreach ($configurationArrays as $configurationArray) {
            $this->argumentReplacerRecipes[] = ArgumentReplacerRecipe::createFromArray($configurationArray);
        }
    }
}

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
use Rector\Rector\Dynamic\Configuration\ArgumentReplacerItemRecipe;

final class ArgumentReplacerRector extends AbstractRector
{
    /**
     * @var ArgumentReplacerItemRecipe[]
     */
    private $argumentReplacerItemRecipes = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var argumentReplacerItemRecipe[]
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
            // @todo constants and own methods
            if ($argumentReplacerItemRecipe->getType() === 'removed') {
                unset($argumentsOrParameters[$argumentReplacerItemRecipe->getPosition()]);

            } elseif ($argumentReplacerItemRecipe->getType() === 'changed') {
                $argumentsOrParameters[$argumentReplacerItemRecipe->getPosition()] = BuilderHelpers::normalizeValue($argumentReplacerItemRecipe->getDefaultValue());
            } elseif ($argumentReplacerItemRecipe->getType() === 'replace_default_value') {
                /** @var Arg $argumentOrParameter */
                $argumentOrParameter = $argumentsOrParameters[$argumentReplacerItemRecipe->getPosition()];
                $resolvedValue = $this->constExprEvaluator->evaluateDirectly($argumentOrParameter->value);

                $replaceMap = $argumentReplacerItemRecipe->getReplaceMap();
                foreach ($replaceMap as $oldValue => $newValue) {
                    if ($resolvedValue === $oldValue) {
                        $argumentsOrParameters[$argumentReplacerItemRecipe->getPosition()] = BuilderHelpers::normalizeValue($newValue);
                    }
                }
            }
        }

        $this->setNodeArgumentsOrParameters($node, $argumentsOrParameters);

        return $node;
    }

    /**
     * @return ArgumentReplacerItemRecipe[]
     */
    private function matchArgumentChanges(Node $node): array
    {
        if (! $node instanceof ClassMethod && ! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return [];
        }

        $argumentReplacerItemRecipes = [];

        foreach ($this->argumentReplacerItemRecipes as $argumentReplacerItemRecipe) {

            if ($this->isTypeAndMethods($node, $argumentReplacerItemRecipe->getClass(), [$argumentReplacerItemRecipe->getMethod()])) {
                $argumentReplacerItemRecipes[] = $argumentReplacerItemRecipe;
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

    /**
     * @param string[] $methods
     */
    private function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if ($this->methodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
            return true;
        }

        if ($this->staticMethodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
            return true;
        }

        return $this->classMethodAnalyzer->isTypeAndMethods($node, $type, $methods);
    }

    private function loadArgumentReplacerItemRecipes(array $configurationArrays): void
    {
        foreach ($configurationArrays as $configurationArray) {
            $this->argumentReplacerItemRecipes[] = ArgumentReplacerItemRecipe::createFromArray($configurationArray);
        }
    }
}

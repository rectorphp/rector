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
use Rector\Configuration\Rector\ArgumentReplacerRecipe;
use Rector\Configuration\Rector\ArgumentReplacerRecipeFactory;
use Rector\NodeAnalyzer\ClassMethodAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentRector extends AbstractRector
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
    private $activeArgumentReplacerRecipes = [];

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
     * @var ArgumentReplacerRecipeFactory
     */
    private $argumentReplacerRecipeFactory;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        MethodCallAnalyzer $methodCallAnalyzer,
        ClassMethodAnalyzer $classMethodAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        ConstExprEvaluator $constExprEvaluator,
        ArgumentReplacerRecipeFactory $argumentReplacerRecipeFactory
    ) {
        $this->argumentReplacerRecipeFactory = $argumentReplacerRecipeFactory;
        $this->loadArgumentReplacerRecipes($argumentChangesByMethodAndType);
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->classMethodAnalyzer = $classMethodAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
        $this->constExprEvaluator = $constExprEvaluator;
    }

    /**
     * @todo complete list with all possibilities
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            '[Dynamic] Adds, removes or replaces defined arguments in defined methods and their calls.',
            [
                new CodeSample(
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
$containerBuilder->compile();',
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
$containerBuilder->compile(true);'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeArgumentReplacerRecipes = $this->matchArgumentChanges($node);

        return (bool) $this->activeArgumentReplacerRecipes;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): Node
    {
        $argumentsOrParameters = $this->getNodeArgumentsOrParameters($node);
        $argumentsOrParameters = $this->processArgumentNodes($argumentsOrParameters);

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

        $argumentReplacerRecipes = [];

        foreach ($this->argumentReplacerRecipes as $argumentReplacerRecipe) {
            if ($this->isNodeToRecipeMatch($node, $argumentReplacerRecipe)) {
                $argumentReplacerRecipes[] = $argumentReplacerRecipe;
            }
        }

        return $argumentReplacerRecipes;
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

    private function isNodeToRecipeMatch(Node $node, ArgumentReplacerRecipe $argumentReplacerRecipe): bool
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
    private function loadArgumentReplacerRecipes(array $configurationArrays): void
    {
        foreach ($configurationArrays as $configurationArray) {
            $this->argumentReplacerRecipes[] = $this->argumentReplacerRecipeFactory->createFromArray(
                $configurationArray
            );
        }
    }

    /**
     * @param mixed[] $argumentNodes
     * @return mixed[]
     */
    private function processArgumentNodes(array $argumentNodes): array
    {
        foreach ($this->activeArgumentReplacerRecipes as $argumentReplacerRecipe) {
            $position = $argumentReplacerRecipe->getPosition();

            if (count($argumentReplacerRecipe->getReplaceMap()) > 0) {
                $argumentNodes[$position] = $this->processReplacedDefaultValue(
                    $argumentNodes[$position],
                    $argumentReplacerRecipe
                );
            } elseif ($argumentReplacerRecipe->getDefaultValue() === null) {
                unset($argumentNodes[$position]);
            } else {
                $argumentNodes[$position] = BuilderHelpers::normalizeValue(
                    $argumentReplacerRecipe->getDefaultValue()
                );
            }
        }

        return $argumentNodes;
    }

    private function processReplacedDefaultValue(Arg $argNode, ArgumentReplacerRecipe $argumentReplacerRecipe): Arg
    {
        $resolvedValue = $this->constExprEvaluator->evaluateDirectly($argNode->value);

        $replaceMap = $argumentReplacerRecipe->getReplaceMap();
        foreach ($replaceMap as $oldValue => $newValue) {
            if ($resolvedValue === $oldValue) {
                return new Arg(BuilderHelpers::normalizeValue($newValue));
            }
        }

        return $argNode;
    }
}

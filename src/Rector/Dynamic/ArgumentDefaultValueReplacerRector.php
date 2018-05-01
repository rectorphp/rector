<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\BuilderHelpers;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentDefaultValueReplacerRecipe;
use Rector\Configuration\Rector\ArgumentDefaultValueReplacerRecipeFactory;
use Rector\NodeAnalyzer\ClassMethodAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentDefaultValueReplacerRector extends AbstractArgumentRector
{
    /**
     * @var ArgumentDefaultValueReplacerRecipe[]
     */
    private $argumentDefaultValueReplacerRecipe = [];

    /**
     * @var ArgumentDefaultValueReplacerRecipe[]
     */
    private $activeArgumentDefaultValueReplacerRecipes = [];

    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    /**
     * @var ArgumentDefaultValueReplacerRecipeFactory
     */
    private $argumentDefaultValueReplacerRecipeFactory;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        MethodCallAnalyzer $methodCallAnalyzer,
        ClassMethodAnalyzer $classMethodAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        ConstExprEvaluator $constExprEvaluator,
        ArgumentDefaultValueReplacerRecipeFactory $argumentDefaultValueReplacerRecipeFactory
    ) {
        parent::__construct($methodCallAnalyzer, $classMethodAnalyzer, $staticMethodCallAnalyzer);
        $this->argumentDefaultValueReplacerRecipeFactory = $argumentDefaultValueReplacerRecipeFactory;
        $this->loadArgumentReplacerRecipes($argumentChangesByMethodAndType);
        $this->constExprEvaluator = $constExprEvaluator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            '[Dynamic] Replaces defined map of arguments in defined methods and their calls.',
            [
                new CodeSample(
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
$container->register("foo", "stdClass")->setScope(ContainerBuilder::SCOPE_PROTOTYPE);',
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
$container->register("foo", "stdClass")->setScope(false);'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isValidInstance($node)) {
            return false;
        }

        $this->activeArgumentDefaultValueReplacerRecipes = $this->matchArgumentChanges($node);

        return (bool) $this->activeArgumentDefaultValueReplacerRecipes;
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
     * @return ArgumentDefaultValueReplacerRecipe[]
     */
    private function matchArgumentChanges(Node $node): array
    {
        $argumentReplacerRecipes = [];

        foreach ($this->argumentDefaultValueReplacerRecipe as $argumentDefaultValueReplacerRecipe) {
            if ($this->isNodeToRecipeMatch($node, $argumentDefaultValueReplacerRecipe)) {
                $argumentReplacerRecipes[] = $argumentDefaultValueReplacerRecipe;
            }
        }

        return $argumentReplacerRecipes;
    }

    /**
     * @param mixed[] $configurationArrays
     */
    private function loadArgumentReplacerRecipes(array $configurationArrays): void
    {
        foreach ($configurationArrays as $configurationArray) {
            $this->argumentDefaultValueReplacerRecipe[] = $this->argumentDefaultValueReplacerRecipeFactory->createFromArray(
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
        foreach ($this->activeArgumentDefaultValueReplacerRecipes as $argumentDefaultValueReplacerRecipe) {
            $position = $argumentDefaultValueReplacerRecipe->getPosition();

            $argumentNodes[$position] = $this->processReplacedDefaultValue(
                $argumentNodes[$position],
                $argumentDefaultValueReplacerRecipe
            );
        }

        return $argumentNodes;
    }

    private function processReplacedDefaultValue(
        Arg $argNode,
        ArgumentDefaultValueReplacerRecipe $argumentDefaultValueReplacerRecipe
    ): Arg {
        $resolvedValue = $this->constExprEvaluator->evaluateDirectly($argNode->value);

        $replaceMap = $argumentDefaultValueReplacerRecipe->getReplacement();
        foreach ($replaceMap as $oldValue => $newValue) {
            if ($resolvedValue === $oldValue) {
                return new Arg(BuilderHelpers::normalizeValue($newValue));
            }
        }

        return $argNode;
    }
}

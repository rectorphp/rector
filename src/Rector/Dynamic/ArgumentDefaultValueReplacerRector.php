<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentDefaultValueReplacerRecipe;
use Rector\Configuration\Rector\ArgumentDefaultValueReplacerRecipeFactory;
use Rector\Node\NodeFactory;
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
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        ConstExprEvaluator $constExprEvaluator,
        ArgumentDefaultValueReplacerRecipeFactory $argumentDefaultValueReplacerRecipeFactory,
        NodeFactory $nodeFactory
    ) {
        $this->loadArgumentReplacerRecipes($argumentDefaultValueReplacerRecipeFactory, $argumentChangesByMethodAndType);
        $this->constExprEvaluator = $constExprEvaluator;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            '[Dynamic] Replaces defined map of arguments in defined methods and their calls.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(false);'
CODE_SAMPLE
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
    private function loadArgumentReplacerRecipes(
        ArgumentDefaultValueReplacerRecipeFactory $argumentDefaultValueReplacerRecipeFactory,
        array $configurationArrays
    ): void {
        foreach ($configurationArrays as $configurationArray) {
            $this->argumentDefaultValueReplacerRecipe[] = $argumentDefaultValueReplacerRecipeFactory->createFromArray(
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
        $valueBefore = $argumentDefaultValueReplacerRecipe->getBefore();

        if (! $this->isMatch($valueBefore, $resolvedValue)) {
            return $argNode;
        }

        $valueAfter = $argumentDefaultValueReplacerRecipe->getAfter();

        // class constants → turn string to composite
        if (Strings::contains($valueAfter, '::')) {
            [$class, $constant] = explode('::', $valueAfter);
            $classConstantFetchNode = $this->nodeFactory->createClassConstant($class, $constant);

            return new Arg($classConstantFetchNode);
        }

        return new Arg(BuilderHelpers::normalizeValue($valueAfter));
    }

    /**
     * @param mixed $oldValue
     * @param mixed $resolvedValue
     */
    private function isMatch($oldValue, $resolvedValue): bool
    {
        if ($oldValue === 'true' && $resolvedValue === true) {
            return true;
        }

        if ($oldValue === 'false' && $resolvedValue === false) {
            return true;
        }

        return $resolvedValue === $oldValue;
    }
}

<?php declare(strict_types=1);

namespace Rector\Rector\Argument;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentDefaultValueReplacerRecipe;
use Rector\Node\NodeFactory;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use SomeClass;

final class ArgumentDefaultValueReplacerRector extends AbstractArgumentRector
{
    /**
     * @var ArgumentDefaultValueReplacerRecipe[]
     */
    private $recipes = [];

    /**
     * @var ArgumentDefaultValueReplacerRecipe[]
     */
    private $activeRecipe = [];

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
        NodeFactory $nodeFactory
    ) {
        foreach ($argumentChangesByMethodAndType as $configurationArray) {
            $this->recipes[] = ArgumentDefaultValueReplacerRecipe::createFromArray($configurationArray);
        }

        $this->constExprEvaluator = $constExprEvaluator;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Replaces defined map of arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(false);'
CODE_SAMPLE
                    ,
                    [
                        '$argumentChangesByMethodAndType' => [
                            'class' => SomeClass::class,
                            'method' => 'someMethod',
                            'position' => 0,
                            'before' => 'SomeClass::OLD_CONSTANT',
                            'after' => 'false',
                        ],
                    ]
                ),
            ]
        );
    }

    public function getNodeType(): string
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): Node
    {
        if (! $this->isValidInstance($node)) {
            return null;
        }
        $this->activeRecipe = $this->matchArgumentChanges($node);
        if ((bool) $this->activeRecipe === false) {
            return null;
        }
        /** @var Arg[] $argumentsOrParameters */
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
        $matchedRecipes = [];

        foreach ($this->recipes as $recipe) {
            if ($this->isNodeToRecipeMatch($node, $recipe)) {
                $matchedRecipes[] = $recipe;
            }
        }

        return $matchedRecipes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @return mixed[]
     */
    private function processArgumentNodes(array $argumentNodes): array
    {
        foreach ($this->activeRecipe as $recipe) {
            if (is_scalar($recipe->getBefore())) {
                // simple 1 argument match
                $argumentNodes = $this->processScalarReplacement($argumentNodes, $recipe);
            } elseif (is_array($recipe->getBefore())) {
                // multiple items in a row match
                $argumentNodes = $this->processArrayReplacement($argumentNodes, $recipe);
            }
        }

        return $argumentNodes;
    }

    private function processArgNode(
        Arg $argNode,
        ArgumentDefaultValueReplacerRecipe $argumentDefaultValueReplacerRecipe
    ): Arg {
        $argumentValue = $this->resolveArgumentValue($argNode);
        $valueBefore = $argumentDefaultValueReplacerRecipe->getBefore();

        if ($argumentValue !== $valueBefore) {
            return $argNode;
        }

        return $this->normalizeValueAfterToArgument($argumentDefaultValueReplacerRecipe->getAfter());
    }

    /**
     * @return mixed
     */
    private function resolveArgumentValue(Arg $argNode)
    {
        $resolvedValue = $this->constExprEvaluator->evaluateDirectly($argNode->value);
        if ($resolvedValue === true) {
            return 'true';
        }

        if ($resolvedValue === false) {
            return 'false';
        }

        return $resolvedValue;
    }

    /**
     * @param mixed $value
     */
    private function normalizeValueAfterToArgument($value): Arg
    {
        // class constants → turn string to composite
        if (Strings::contains($value, '::')) {
            [$class, $constant] = explode('::', $value);
            $classConstantFetchNode = $this->nodeFactory->createClassConstant($class, $constant);

            return new Arg($classConstantFetchNode);
        }

        return new Arg(BuilderHelpers::normalizeValue($value));
    }

    /**
     * @param Arg[] $argumentNodes
     * @return Arg[]
     */
    private function processScalarReplacement(
        array $argumentNodes,
        ArgumentDefaultValueReplacerRecipe $argumentDefaultValueReplacerRecipe
    ): array {
        $argumentNodes[$argumentDefaultValueReplacerRecipe->getPosition()] = $this->processArgNode(
            $argumentNodes[$argumentDefaultValueReplacerRecipe->getPosition()],
            $argumentDefaultValueReplacerRecipe
        );

        return $argumentNodes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @return Arg[]
     */
    private function processArrayReplacement(
        array $argumentNodes,
        ArgumentDefaultValueReplacerRecipe $argumentDefaultValueReplacerRecipe
    ): array {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe(
            $argumentNodes,
            $argumentDefaultValueReplacerRecipe
        );

        if ($argumentValues !== $argumentDefaultValueReplacerRecipe->getBefore()) {
            return $argumentNodes;
        }

        if (is_string($argumentDefaultValueReplacerRecipe->getAfter())) {
            $argumentNodes[$argumentDefaultValueReplacerRecipe->getPosition()] = $this->normalizeValueAfterToArgument(
                $argumentDefaultValueReplacerRecipe->getAfter()
            );

            // clear following arguments
            $argumentCountToClear = count($argumentDefaultValueReplacerRecipe->getBefore()) - 1;
            for ($i = 1; $i <= $argumentCountToClear; ++$i) {
                $position = $argumentDefaultValueReplacerRecipe->getPosition() + $i;
                unset($argumentNodes[$position]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @return mixed
     */
    private function resolveArgumentValuesToBeforeRecipe(
        array $argumentNodes,
        ArgumentDefaultValueReplacerRecipe $argumentDefaultValueReplacerRecipe
    ) {
        $argumentValues = [];

        $beforeArgumentCount = count($argumentDefaultValueReplacerRecipe->getBefore());

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (isset($argumentNodes[$argumentDefaultValueReplacerRecipe->getPosition() + $i])) {
                $argumentValues[] = $this->resolveArgumentValue(
                    $argumentNodes[$argumentDefaultValueReplacerRecipe->getPosition() + $i]
                );
            }
        }

        return $argumentValues;
    }
}

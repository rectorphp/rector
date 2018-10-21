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
use Rector\Node\NodeFactory;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentDefaultValueReplacerRector extends AbstractArgumentRector
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var mixed[]
     */
    private $replacesByMethodAndTypes = [];

    /**
     * @param mixed[] $replacesByMethodAndTypes
     */
    public function __construct(
        array $replacesByMethodAndTypes,
        ConstExprEvaluator $constExprEvaluator,
        NodeFactory $nodeFactory
    ) {
        $this->constExprEvaluator = $constExprEvaluator;
        $this->nodeFactory = $nodeFactory;
        $this->replacesByMethodAndTypes = $replacesByMethodAndTypes;
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
                            'SomeExampleClass' => [
                                'someMethod' => [
                                    0 => [
                                        [
                                            'before' => 'SomeClass::OLD_CONSTANT',
                                            'after' => 'false',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->replacesByMethodAndTypes as $type => $replacesByMethods) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($replacesByMethods as $method => $replaces) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                $this->processReplaces($node, $replaces);
            }
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param mixed[] $replaces
     */
    private function processReplaces(Node $node, array $replaces): Node
    {
        foreach ($replaces as $position => $oldToNewValues) {
            if ($node instanceof ClassMethod) {
                if (! isset($node->params[$position])) {
                    continue;
                }

                // @todo
            } elseif (isset($node->args[$position])) {
                $this->processArgs($node, $position, $oldToNewValues);
            }
        }

        return $node;
    }

    /**
     * @param mixed $value
     */
    private function normalizeValueToArgument($value): Arg
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
     * @param Arg[] $argumentNodes
     * @param mixed[] $before
     * @param mixed|mixed[] $after
     * @return Arg[]|null
     */
    private function processArrayReplacement(array $argumentNodes, int $position, array $before, $after): ?array
    {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $position, $before);
        if ($argumentValues !== $before) {
            return null;
        }

        if (is_string($after)) {
            $argumentNodes[$position] = $this->normalizeValueToArgument($after);

            // clear following arguments
            $argumentCountToClear = count($before);
            for ($i = $position + 1; $i <= ($position + $argumentCountToClear); ++$i) {
                unset($argumentNodes[$i]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @param mixed[] $before
     * @return mixed
     */
    private function resolveArgumentValuesToBeforeRecipe(array $argumentNodes, int $position, array $before)
    {
        $argumentValues = [];

        $beforeArgumentCount = count($before);

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (isset($argumentNodes[$position + $i])) {
                $argumentValues[] = $this->resolveArgumentValue($argumentNodes[$position + $i]);
            }
        }

        return $argumentValues;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param mixed[] $oldToNewValues
     */
    private function processArgs(Node $node, int $position, array $oldToNewValues): void
    {
        $argValue = $this->resolveArgumentValue($node->args[$position]);
        foreach ($oldToNewValues as $oldToNewValue) {
            if (is_scalar($oldToNewValue['before']) && $argValue === $oldToNewValue['before']) {
                $node->args[$position] = $this->normalizeValueToArgument($oldToNewValue['after']);
            } elseif (is_array($oldToNewValue['before'])) {
                $newArgs = $this->processArrayReplacement(
                    $node->args,
                    $position,
                    $oldToNewValue['before'],
                    $oldToNewValue['after']
                );

                if ($newArgs) {
                    $node->args = array_values($newArgs);
                    break;
                }
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\ArgumentDefaultValueReplacerRectorTest
 */
final class ArgumentDefaultValueReplacerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REPLACED_ARGUMENTS = 'replaced_arguments';

    /**
     * @var ArgumentDefaultValueReplacer[]
     */
    private $replacedArguments = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
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
                        self::REPLACED_ARGUMENTS => [
                            new ArgumentDefaultValueReplacer(
                                'SomeExampleClass',
                                'someMethod',
                                0,
                                'SomeClass::OLD_CONSTANT',
                                'false'
                            ),
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
        foreach ($this->replacedArguments as $replacedArgument) {
            if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
                $node,
                $replacedArgument->getClass()
            )) {
                continue;
            }

            if (! $this->isName($node->name, $replacedArgument->getMethod())) {
                continue;
            }

            $this->processReplaces($node, $replacedArgument);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $replacedArguments = $configuration[self::REPLACED_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($replacedArguments, ArgumentDefaultValueReplacer::class);
        $this->replacedArguments = $replacedArguments;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function processReplaces(Node $node, ArgumentDefaultValueReplacer $argumentDefaultValueReplacer): ?Node
    {
        if ($node instanceof ClassMethod) {
            if (! isset($node->params[$argumentDefaultValueReplacer->getPosition()])) {
                return null;
            }
        } elseif (isset($node->args[$argumentDefaultValueReplacer->getPosition()])) {
            $this->processArgs($node, $argumentDefaultValueReplacer);
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processArgs(Node $node, ArgumentDefaultValueReplacer $argumentDefaultValueReplacer): void
    {
        $position = $argumentDefaultValueReplacer->getPosition();

        $argValue = $this->valueResolver->getValue($node->args[$position]->value);

        if (is_scalar(
            $argumentDefaultValueReplacer->getValueBefore()
        ) && $argValue === $argumentDefaultValueReplacer->getValueBefore()) {
            $node->args[$position] = $this->normalizeValueToArgument($argumentDefaultValueReplacer->getValueAfter());
        } elseif (is_array($argumentDefaultValueReplacer->getValueBefore())) {
            $newArgs = $this->processArrayReplacement($node->args, $argumentDefaultValueReplacer);

            if ($newArgs) {
                $node->args = $newArgs;
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function normalizeValueToArgument($value): Arg
    {
        // class constants → turn string to composite
        if (is_string($value) && Strings::contains($value, '::')) {
            [$class, $constant] = explode('::', $value);
            $classConstFetch = $this->nodeFactory->createClassConstFetch($class, $constant);

            return new Arg($classConstFetch);
        }

        return new Arg(BuilderHelpers::normalizeValue($value));
    }

    /**
     * @param Arg[] $argumentNodes
     * @return Arg[]|null
     */
    private function processArrayReplacement(
        array $argumentNodes,
        ArgumentDefaultValueReplacer $argumentDefaultValueReplacer
    ): ?array {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $argumentDefaultValueReplacer);
        if ($argumentValues !== $argumentDefaultValueReplacer->getValueBefore()) {
            return null;
        }

        if (is_string($argumentDefaultValueReplacer->getValueAfter())) {
            $argumentNodes[$argumentDefaultValueReplacer->getPosition()] = $this->normalizeValueToArgument(
                $argumentDefaultValueReplacer->getValueAfter()
            );

            // clear following arguments
            $argumentCountToClear = count($argumentDefaultValueReplacer->getValueBefore());
            for ($i = $argumentDefaultValueReplacer->getPosition() + 1; $i <= $argumentDefaultValueReplacer->getPosition() + $argumentCountToClear; ++$i) {
                unset($argumentNodes[$i]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @return mixed[]
     */
    private function resolveArgumentValuesToBeforeRecipe(
        array $argumentNodes,
        ArgumentDefaultValueReplacer $argumentDefaultValueReplacer
    ): array {
        $argumentValues = [];

        /** @var mixed[] $valueBefore */
        $valueBefore = $argumentDefaultValueReplacer->getValueBefore();
        $beforeArgumentCount = count($valueBefore);

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (! isset($argumentNodes[$argumentDefaultValueReplacer->getPosition() + $i])) {
                continue;
            }

            $nextArg = $argumentNodes[$argumentDefaultValueReplacer->getPosition() + $i];
            $argumentValues[] = $this->valueResolver->getValue($nextArg->value);
        }

        return $argumentValues;
    }
}

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
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\ReplacedArgument;
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
     * @var ReplacedArgument[]
     */
    private $replacedArguments = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Replaces defined map of arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
PHP
                    ,
                    <<<'PHP'
$someObject = new SomeClass;
$someObject->someMethod(false);'
PHP
                    ,
                    [
                        self::REPLACED_ARGUMENTS => [
                            new ReplacedArgument(
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
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $replacedArgument->getClass())) {
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
        Assert::allIsInstanceOf($replacedArguments, ReplacedArgument::class);
        $this->replacedArguments = $replacedArguments;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function processReplaces(Node $node, ReplacedArgument $replacedArgument): ?Node
    {
        if ($node instanceof ClassMethod) {
            if (! isset($node->params[$replacedArgument->getPosition()])) {
                return null;
            }
        } elseif (isset($node->args[$replacedArgument->getPosition()])) {
            $this->processArgs($node, $replacedArgument);
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processArgs(Node $node, ReplacedArgument $replacedArgument): void
    {
        $position = $replacedArgument->getPosition();

        $argValue = $this->getValue($node->args[$position]->value);

        if (is_scalar($replacedArgument->getValueBefore()) && $argValue === $replacedArgument->getValueBefore()) {
            $node->args[$position] = $this->normalizeValueToArgument($replacedArgument->getValueAfter());
        } elseif (is_array($replacedArgument->getValueBefore())) {
            $newArgs = $this->processArrayReplacement($node->args, $replacedArgument);

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
            $classConstFetch = $this->createClassConstFetch($class, $constant);

            return new Arg($classConstFetch);
        }

        return new Arg(BuilderHelpers::normalizeValue($value));
    }

    /**
     * @param Arg[] $argumentNodes
     * @return Arg[]|null
     */
    private function processArrayReplacement(array $argumentNodes, ReplacedArgument $replacedArgument): ?array
    {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $replacedArgument);
        if ($argumentValues !== $replacedArgument->getValueBefore()) {
            return null;
        }

        if (is_string($replacedArgument->getValueAfter())) {
            $argumentNodes[$replacedArgument->getPosition()] = $this->normalizeValueToArgument(
                $replacedArgument->getValueAfter()
            );

            // clear following arguments
            $argumentCountToClear = count($replacedArgument->getValueBefore());
            for ($i = $replacedArgument->getPosition() + 1; $i <= $replacedArgument->getPosition() + $argumentCountToClear; ++$i) {
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
        ReplacedArgument $replacedArgument
    ): array {
        $argumentValues = [];

        /** @var mixed[] $valueBefore */
        $valueBefore = $replacedArgument->getValueBefore();
        $beforeArgumentCount = count($valueBefore);

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (! isset($argumentNodes[$replacedArgument->getPosition() + $i])) {
                continue;
            }

            $nextArg = $argumentNodes[$replacedArgument->getPosition() + $i];
            $argumentValues[] = $this->getValue($nextArg->value);
        }

        return $argumentValues;
    }
}

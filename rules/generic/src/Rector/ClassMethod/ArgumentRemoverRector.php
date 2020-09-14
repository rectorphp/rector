<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\ArgumentRemover;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ArgumentRemoverRector\ArgumentRemoverRectorTest
 */
final class ArgumentRemoverRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REMOVED_ARGUMENTS = 'removed_arguments';

    /**
     * @var ArgumentRemover[]
     */
    private $removedArguments = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes defined arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(true);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod();'
CODE_SAMPLE
                    ,
                    [
                        self::REMOVED_ARGUMENTS => [new ArgumentRemover('ExampleClass', 'someMethod', 0, 'true')],
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
        foreach ($this->removedArguments as $removedArgument) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $removedArgument->getClass())) {
                continue;
            }

            if (! $this->isName($node->name, $removedArgument->getMethod())) {
                continue;
            }

            $this->processPosition($node, $removedArgument);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $removedArguments = $configuration[self::REMOVED_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($removedArguments, ArgumentRemover::class);
        $this->removedArguments = $removedArguments;
    }

    /**
     * @param ClassMethod|StaticCall|MethodCall $node
     */
    private function processPosition(Node $node, ArgumentRemover $argumentRemover): void
    {
        if ($argumentRemover->getValue() === null) {
            if ($node instanceof MethodCall || $node instanceof StaticCall) {
                unset($node->args[$argumentRemover->getPosition()]);
            } else {
                unset($node->params[$argumentRemover->getPosition()]);
            }

            return;
        }

        $match = $argumentRemover->getValue();
        if (isset($match['name'])) {
            $this->removeByName($node, $argumentRemover->getPosition(), $match['name']);
            return;
        }

        // only argument specific value can be removed
        if ($node instanceof ClassMethod || ! isset($node->args[$argumentRemover->getPosition()])) {
            return;
        }

        if ($this->isArgumentValueMatch($node->args[$argumentRemover->getPosition()], $match)) {
            unset($node->args[$argumentRemover->getPosition()]);
        }
    }

    /**
     * @param ClassMethod|StaticCall|MethodCall $node
     */
    private function removeByName(Node $node, int $position, string $name): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if (isset($node->args[$position]) && $this->isName($node->args[$position], $name)) {
                $this->removeArg($node, $position);
            }

            return;
        }

        if ($node instanceof ClassMethod) {
            if (isset($node->params[$position]) && $this->isName($node->params[$position], $name)) {
                $this->removeParam($node, $position);
            }

            return;
        }
    }

    /**
     * @param mixed[] $values
     */
    private function isArgumentValueMatch(Arg $arg, array $values): bool
    {
        $nodeValue = $this->getValue($arg->value);

        return in_array($nodeValue, $values, true);
    }
}

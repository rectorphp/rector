<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\NodeFactory\ClassConstantFetchValueFactory;
use Rector\Privatization\ValueObject\ReplaceStringWithClassConstant;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\ReplaceStringWithClassConstantRectorTest
 */
final class ReplaceStringWithClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REPLACE_STRING_WITH_CLASS_CONSTANT = 'replace_string_with_class_constant';

    /**
     * @var ReplaceStringWithClassConstant[]
     */
    private $replaceStringWithClassConstants = [];

    /**
     * @var ClassConstantFetchValueFactory
     */
    private $classConstantFetchValueFactory;

    public function __construct(ClassConstantFetchValueFactory $classConstantFetchValueFactory)
    {
        $this->classConstantFetchValueFactory = $classConstantFetchValueFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace string values in specific method call by constant of provided class', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->call('name');
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->call(Placeholder::NAME);
    }
}
CODE_SAMPLE
,
                [
                    self::REPLACE_STRING_WITH_CLASS_CONSTANT => [
                        new ReplaceStringWithClassConstant('SomeClass', 'call', 0, 'Placeholder'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->args === []) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->replaceStringWithClassConstants as $replaceStringWithClassConstant) {
            $desiredArg = $this->matchArg($node, $replaceStringWithClassConstant);
            if (! $desiredArg instanceof Arg) {
                continue;
            }

            $classConstFetch = $this->classConstantFetchValueFactory->create(
                $desiredArg->value,
                $replaceStringWithClassConstant->getClassWithConstants()
            );

            if (! $classConstFetch instanceof ClassConstFetch) {
                continue;
            }

            $desiredArg->value = $classConstFetch;
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->replaceStringWithClassConstants = $configuration[self::REPLACE_STRING_WITH_CLASS_CONSTANT] ?? [];
    }

    private function matchArg(
        MethodCall $methodCall,
        ReplaceStringWithClassConstant $replaceStringWithClassConstant
    ): ?Arg {
        if (! $this->isOnClassMethodCall(
            $methodCall,
            $replaceStringWithClassConstant->getClass(),
            $replaceStringWithClassConstant->getMethod()
        )) {
            return null;
        }

        $desiredArg = $methodCall->args[$replaceStringWithClassConstant->getArgPosition()] ?? null;
        if (! $desiredArg instanceof Arg) {
            return null;
        }

        if ($desiredArg->value instanceof ClassConstFetch) {
            return null;
        }

        return $desiredArg;
    }
}

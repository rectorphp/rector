<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
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
    public const METHOD_CALL_WITH_POSITION_AND_CLASS_CONSTANTS = 'method_call_with_position_and_class_constants';

    /**
     * @var mixed[]
     */
    private $methodcallwithpositionandclassconstants = [];

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
                    self::methodCallWithPositionAndClassConstants => [
                        'before' => 'after',
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
        // change the node

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->methodcallwithpositionandclassconstants = $configuration[self::methodCallWithPositionAndClassConstants] ?? [];
    }
}

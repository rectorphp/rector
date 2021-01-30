<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\ReplaceParentCallByPropertyCallRectorTest
 */
final class ReplaceParentCallByPropertyCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PARENT_CALLS_TO_PROPERTIES = 'parent_calls_to_properties';

    /**
     * @var ReplaceParentCallByPropertyCall[]
     */
    private $parentCallToProperties = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes method calls in child of specific types to defined property method call', [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $someTypeToReplace->someMethodCall();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(SomeTypeToReplace $someTypeToReplace)
    {
        $this->someProperty->someMethodCall();
    }
}
CODE_SAMPLE
                    ,
                    [
                        self::PARENT_CALLS_TO_PROPERTIES => [
                            new ReplaceParentCallByPropertyCall('SomeTypeToReplace', 'someMethodCall', 'someProperty'),
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->parentCallToProperties as $parentCallToProperty) {
            if (! $this->isObjectType($node->var, $parentCallToProperty->getClass())) {
                continue;
            }

            if (! $this->isName($node->name, $parentCallToProperty->getMethod())) {
                continue;
            }

            $node->var = $this->nodeFactory->createPropertyFetch('this', $parentCallToProperty->getProperty());
            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->parentCallToProperties = $configuration[self::PARENT_CALLS_TO_PROPERTIES] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\ReplaceParentCallByPropertyCallRectorTest
 */
final class ReplaceParentCallByPropertyCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PARENT_TYPE_TO_METHOD_NAME_TO_PROPERTY_FETCH = 'parnet_type_to_method_name_to_property_fetch';

    /**
    AddPropertyByParentRector.php:103
     * @var array<string, array<string, string>>
     */
    private $replaceParentCallByPropertyArguments = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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
                        self::PARENT_TYPE_TO_METHOD_NAME_TO_PROPERTY_FETCH => [
                            'SomeTypeToReplace' => [
                                'someMethodCall' => 'someProperty',
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->replaceParentCallByPropertyArguments as $type => $methodNamesToPropertyNames) {
            if (! $this->isObjectType($node->var, $type)) {
                continue;
            }

            foreach ($methodNamesToPropertyNames as $methodName => $propertyName) {
                if (! $this->isName($node->name, $methodName)) {
                    continue;
                }

                $node->var = $this->createPropertyFetch('this', $propertyName);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->replaceParentCallByPropertyArguments = $configuration[self::PARENT_TYPE_TO_METHOD_NAME_TO_PROPERTY_FETCH] ?? [];
    }
}

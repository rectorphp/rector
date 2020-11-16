<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ConfigurableRectorRule\Fixture;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Transform\ValueObject\StaticCallToFuncCall;

final class ImplementsAndHasConfiguredCodeSampleRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STATIC_CALLS_TO_FUNCTIONS = 'static_calls_to_functions';

    public function configure(array $configuration): void
    {
        // TODO: Implement configure() method.
    }

    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns static call to function call.', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(
                'OldClass::oldMethod("args");',
                'new_function("args");',
                [
                    self::STATIC_CALLS_TO_FUNCTIONS => [
                        new StaticCallToFuncCall('OldClass', 'oldMethod', 'new_function'),
                    ],
                ]
            ),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ConfigurableRectorRule\Fixture;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\Transform\ValueObject\StaticCallToFuncCall;

final class ImplementsAndHasNoConfiguredCodeSampleRector implements ConfigurableRectorInterface
{
    public function configure(array $configuration): void
    {
        // TODO: Implement configure() method.
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns static call to function call.', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                'OldClass::oldMethod("args");',
                'new_function("args");'
            ),
        ]);
    }
}

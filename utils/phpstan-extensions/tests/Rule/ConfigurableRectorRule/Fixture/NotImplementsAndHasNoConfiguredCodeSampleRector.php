<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ConfigurableRectorRule\Fixture;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Transform\ValueObject\StaticCallToFuncCall;

final class NotImplementsAndHasNoConfiguredCodeSampleRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns static call to function call.', [
            new CodeSample(
                'OldClass::oldMethod("args");',
                'new_function("args");'
            ),
        ]);
    }
}

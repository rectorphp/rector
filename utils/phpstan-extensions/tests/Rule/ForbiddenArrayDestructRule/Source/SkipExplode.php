<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayDestructRule\Source;

final class SkipExplode
{
    public function run()
    {
        [$one, $two] = explode('::', 'SomeClass::SOME_CONSTANTS');
    }
}

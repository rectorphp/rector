<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayDestructRule\Source;

final class SkipSwap
{
    public function run($one, $two)
    {
        [$one, $two] = [$two, $one];
    }
}

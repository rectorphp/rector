<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class SkipEmptyFirstArray
{
    public function refactor($node)
    {
        [, $comparedNode] = $node;
    }
}

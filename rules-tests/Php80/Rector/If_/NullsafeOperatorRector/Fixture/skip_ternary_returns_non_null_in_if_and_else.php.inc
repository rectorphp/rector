<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class SkipTernaryReturnsNonNullInIfAndElse
{
    public function ternary()
    {
        $b = $a === null ? 'if case' : 'else case';
        $b = $a !== null ? 'if case 2' : 'else case 2';
        $b = $a === null ? 'is null' : $a->getB();
    }
}
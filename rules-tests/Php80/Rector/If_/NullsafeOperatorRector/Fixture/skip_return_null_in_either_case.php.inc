<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class SkipReturnNullInEitherCase
{
    public function f()
    {
        $otherVar = null;

        if ($otherVar === null) {
            return null;
        }

        return null;
    }

}
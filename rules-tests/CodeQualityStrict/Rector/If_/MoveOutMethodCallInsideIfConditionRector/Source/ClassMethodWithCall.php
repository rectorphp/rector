<?php

declare(strict_types=1);

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source;

final class ClassMethodWithCall
{
    public function condition($arg): bool
    {
        return mt_rand(0, 1) ? true : false;
    }
}

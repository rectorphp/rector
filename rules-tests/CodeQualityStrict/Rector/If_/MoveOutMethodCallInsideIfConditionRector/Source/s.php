<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Source;

function s()
{
    return new class {
        public function bytesAt($arg)
        {
            return true;
        }
    };
}

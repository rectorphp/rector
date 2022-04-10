<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

use DateTimeImmutable;

final class SkipVariableUsedWithDateTimeImmutable
{
    public function run()
    {
        $dateTime = new DateTimeImmutable();
        $dateTime = $dateTime->setTime(14, 0, 0);

        return $dateTime;
    }
}

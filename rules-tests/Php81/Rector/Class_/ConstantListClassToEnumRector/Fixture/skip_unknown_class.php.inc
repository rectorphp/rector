<?php

namespace Rector\Tests\Php81\Rector\Class_\ConstantListClassToEnumRector\Fixture;

final class SkipUnknownClass
{
    /**
     * @param AnythingNonExisting::* $gear
     */
    public function changeGear(string $gear)
    {
    }
}

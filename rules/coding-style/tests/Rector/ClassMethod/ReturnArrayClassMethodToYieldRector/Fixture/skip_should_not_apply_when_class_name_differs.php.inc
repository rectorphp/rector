<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

final class SkipShouldNotApplyWhenClassNameDiffers
{
    public function getSubscribedEvents(): array
    {
        return ['some' => 'event'];
    }

    public function provideSomeData(): array
    {
        return ['some' => 'data'];
    }
}

<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

final class SkipShouldNotApplyWhenMethodNameDiffers extends ParentTestCase
{
    public function getSomePublicData(): array
    {
        return ['visibility' => 'public'];
    }

    private function getSomePrivateData(): array
    {
        return ['visibility' => 'private'];
    }
}

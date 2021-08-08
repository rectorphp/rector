<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\Fixture;

trait SkipNonAbstractPrivate
{
    private function doNotTouch()
    {
    }
}
?>

<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\SomeContainerInterface;

class KeepInterfaceDonwgraded implements SomeContainerInterface
{
    public function get($id)
    {
    }
}

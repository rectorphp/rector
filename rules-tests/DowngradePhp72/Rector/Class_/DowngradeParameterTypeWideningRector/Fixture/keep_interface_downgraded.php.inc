<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source\SomeContainerInterface;

class KeepInterfaceDonwgraded implements SomeContainerInterface
{
    public function get($id)
    {
    }
}

<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source\SomeExternalContainerInterface;

class KeepInterfaceDonwgraded implements SomeExternalContainerInterface
{
    public function get($id)
    {
    }
}

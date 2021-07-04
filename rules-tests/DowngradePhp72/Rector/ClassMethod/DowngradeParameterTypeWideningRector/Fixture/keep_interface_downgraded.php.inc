<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\SomeExternalContainerInterface;

class KeepInterfaceDonwgraded implements SomeExternalContainerInterface
{
    public function get($id)
    {
    }
}

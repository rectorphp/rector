<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Source\SomeContractWithGo;

class SkipContract implements SomeContractWithGo
{
    public function go()
    {
    }
}

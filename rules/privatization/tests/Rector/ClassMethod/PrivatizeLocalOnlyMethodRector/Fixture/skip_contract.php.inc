<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Source\SomeContractWithGo;

class SkipContract implements SomeContractWithGo
{
    public function go()
    {
    }
}

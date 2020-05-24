<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

use Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\AbstractClassWithProtectedProperty;

final class KeepParentProtected extends AbstractClassWithProtectedProperty
{
    protected $value = 100;
}

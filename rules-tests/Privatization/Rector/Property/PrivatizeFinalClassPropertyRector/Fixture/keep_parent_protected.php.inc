<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

use Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Source\AbstractClassWithProtectedProperty;

final class KeepParentProtected extends AbstractClassWithProtectedProperty
{
    protected $value = 100;
}

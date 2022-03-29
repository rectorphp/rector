<?php

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

use Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Source\AbstractClassWithProtectedProperty;

final class KeepParentStaticProtectedUsedByParent extends AbstractClassWithProtectedProperty
{
    protected static $valueStatic = 100;
    protected static $valueStatic2 = 100;
    protected static $valueStatic3 = 100;
}

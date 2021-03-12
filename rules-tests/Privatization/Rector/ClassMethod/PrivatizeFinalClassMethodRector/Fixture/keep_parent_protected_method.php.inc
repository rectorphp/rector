<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\AbstractClassWithProtectedClassMethod;

final class KeepParentProtected extends AbstractClassWithProtectedClassMethod
{
    protected function getName()
    {
    }
}

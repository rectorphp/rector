<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\AbstractClassWithProtectedClassMethod;

final class KeepParentProtected extends AbstractClassWithProtectedClassMethod
{
    protected function getName()
    {
    }
}

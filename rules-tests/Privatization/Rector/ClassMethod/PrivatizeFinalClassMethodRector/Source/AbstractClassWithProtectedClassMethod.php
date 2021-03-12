<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source;

abstract class AbstractClassWithProtectedClassMethod
{
    protected function getName()
    {
    }
}

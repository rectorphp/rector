<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source;

abstract class AbstractClassWithProtectedClassMethod
{
    protected function getName()
    {
    }
}

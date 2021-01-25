<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\MethodSomeTrait;

final class SkipTraitCalled
{
    use MethodSomeTrait;

    protected function configureRoutes()
    {
    }
}

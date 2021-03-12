<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\MethodSomeTrait;

final class SkipTraitCalled
{
    use MethodSomeTrait;

    protected function configureRoutes()
    {
    }
}

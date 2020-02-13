<?php

declare(strict_types=1);


namespace Rector\PHPStanExtensions\Tests\Rule\ClassMethod\Source;

final class ClassWithOverridingVisibility extends GoodVisibility
{
    public function run()
    {
    }
}

abstract class GoodVisibility
{
    protected function run()
    {

    }
}

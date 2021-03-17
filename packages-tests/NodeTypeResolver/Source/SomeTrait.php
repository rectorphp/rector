<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\Source;

trait SomeTrait
{
    public function getSomeClass(): SomeClass
    {
        return new SomeClass();
    }
}

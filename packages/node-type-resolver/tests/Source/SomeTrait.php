<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\Source;

trait SomeTrait
{
    public function getSomeClass(): SomeClass
    {
        return new SomeClass();
    }
}

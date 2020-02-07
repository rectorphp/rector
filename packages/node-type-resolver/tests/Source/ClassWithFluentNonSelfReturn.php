<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\Source;

final class ClassWithFluentNonSelfReturn
{
    public function createAnotherClass(): AnotherClass
    {
    }
}

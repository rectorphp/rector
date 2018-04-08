<?php

namespace SomeNamespace;

use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

class ThisClass extends AnotherClass
{
    public function getThis()
    {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source;

use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

class ThisClass extends AnotherClass
{
    public function getThis()
    {
        return $this;
    }
}

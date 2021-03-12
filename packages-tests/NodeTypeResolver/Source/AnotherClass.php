<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\Source;

class AnotherClass
{
    public function getParameters()
    {
    }

    /**
     * @return static
     */
    public function callAndReturnSelf()
    {
    }
}

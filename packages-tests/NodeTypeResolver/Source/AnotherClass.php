<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\Source;

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

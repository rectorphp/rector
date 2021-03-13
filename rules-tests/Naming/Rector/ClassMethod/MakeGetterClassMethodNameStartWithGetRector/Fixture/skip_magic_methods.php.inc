<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

class SkipMagicMethods
{
    /**
     * @var string
     */
    private $name;

    public function __toString(): string
    {
        return $this->name;
    }
}

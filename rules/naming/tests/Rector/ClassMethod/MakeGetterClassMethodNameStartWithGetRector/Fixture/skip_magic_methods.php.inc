<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

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

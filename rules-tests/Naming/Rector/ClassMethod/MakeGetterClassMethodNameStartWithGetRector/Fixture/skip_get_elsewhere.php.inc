<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

class SkipGetElsewhere
{
    /**
     * @var string
     */
    private $name;

    public function getSomeName(): string
    {
        return $this->name;
    }
}

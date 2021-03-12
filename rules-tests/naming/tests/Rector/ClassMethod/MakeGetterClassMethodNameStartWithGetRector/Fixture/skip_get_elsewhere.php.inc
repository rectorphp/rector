<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

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

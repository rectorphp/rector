<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

class SkipIsser
{
    /**
     * @var bool
     */
    private $isLegal = true;

    public function isLegal(): bool
    {
        return $this->isLegal;
    }
}

<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipPropertyArrayFilledByConstructViaMethodCall
{
    /** @var array */
    private $property;

    public function __construct()
    {
        $this->fill();
    }

    private function fill()
    {
        $this->property = [];
    }

    public function run(): int
    {
        return count($this->property);
    }
}

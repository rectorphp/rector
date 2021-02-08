<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

class skipClassExtend
{
    public function run(): void
    {
        echo (new Car())->hasEngine();
    }
}

class Car extends Vehicle
{
    /**
     * @var string
     */
    private $engineSize;
}

class Vehicle
{
    /**
     * @var string
     */
    protected $colour;

    /**
     * @var bool
     */
    protected $engine;

    public function hasEngine(): bool
    {
        return $this->engine;
    }
}

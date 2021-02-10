<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

class SkipClassExtend extends Vehicle
{
    /**
     * @var string
     */
    private $engineSize;

    public function run(): void
    {
        echo (new self())->hasEngine();
    }
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

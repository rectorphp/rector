<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

trait SkipTrait
{
    /**
     * @var string[]
     */
    private $magicMethods = [
        '__toString',
        '__wakeup',
    ];

    public function run()
    {
        foreach ($this->magicMethods as $magicMethod) {
            echo $magicMethod;
        }
    }
}

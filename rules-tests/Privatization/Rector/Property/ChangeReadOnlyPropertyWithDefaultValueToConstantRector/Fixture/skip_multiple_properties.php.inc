<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class SkipMultipleProperties
{
    /**
     * @var string[]
     */
    private $moreMagic, $magicMethods = [
        '__toString',
        '__wakeup',
    ];
}

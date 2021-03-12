<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

final class SkipMagicNameCall
{
    /**
     * @var EmptyMethod
     */
    private $emptyMethod;

    public function __construct(EmptyMethod $emptyMethod)
    {
        $this->emptyMethod = $emptyMethod;
    }

    public function execute($magic)
    {
        $magic = 'one' ? 'two' : 'three';

        $this->emptyMethod->{$magic}();
    }
}

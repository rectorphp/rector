<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

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

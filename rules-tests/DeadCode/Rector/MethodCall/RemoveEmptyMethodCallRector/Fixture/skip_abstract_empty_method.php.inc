<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\AbstractEmptyMethod;

final class SkipAbstractEmptyMethod
{
    /**
     * @var AbstractEmptyMethod
     */
    private $abstractEmptyMethod;

    public function __construct(AbstractEmptyMethod $abstractEmptyMethod)
    {
        $this->abstractEmptyMethod = $abstractEmptyMethod;
    }

    public function run()
    {
        $this->abstractEmptyMethod->veryEmpty();
    }
}

<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\AbstractEmptyMethod;

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

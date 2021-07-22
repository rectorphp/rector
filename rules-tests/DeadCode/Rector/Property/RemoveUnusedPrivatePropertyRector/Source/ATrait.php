<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Source;

trait ATrait
{
    public function run()
    {
        return $this->usedProperty;
    }

    public function run2()
    {
        return static::$usedstaticProperty;
    }
}

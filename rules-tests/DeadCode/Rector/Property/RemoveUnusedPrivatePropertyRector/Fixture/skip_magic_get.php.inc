<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipMagicGet
{
    private $packageName = 'test';

    public function __get($name)
    {
        return $this->$name;
    }
}

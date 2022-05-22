<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\Fixture;

final class SkipUsedInCompact
{
    public $data;

    public function __construct($hey, $man)
    {
        $this->data = compact('hey', 'man');
    }
}

<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use SplFileInfo;
use stdClass;

class KeepNative
{
    public function __construct(stdClass $configuration, SplFileInfo $input)
    {
    }
}

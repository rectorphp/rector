<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use SplFileInfo;
use stdClass;

class KeepNative
{
    public function __construct(stdClass $configuration, SplFileInfo $input)
    {
    }
}

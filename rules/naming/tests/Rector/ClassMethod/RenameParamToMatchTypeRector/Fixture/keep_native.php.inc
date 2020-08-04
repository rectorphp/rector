<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use SplFileInfo;
use stdClass;

class KeepNative
{
    public function __construct(stdClass $configuration, SplFileInfo $input)
    {
    }
}

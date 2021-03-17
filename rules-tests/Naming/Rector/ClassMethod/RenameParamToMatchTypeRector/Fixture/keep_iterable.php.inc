<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\AMP;
use DateTime;

class KeepIterable
{
    /**
     * @var iterable
     */
    public $nodes = [];

    public function iterateMe(iterable $variable)
    {
    }
}

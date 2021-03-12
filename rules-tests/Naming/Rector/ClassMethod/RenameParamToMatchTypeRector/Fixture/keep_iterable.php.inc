<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\AMP;
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

<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\AMP;
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

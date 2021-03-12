<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\AMP;
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

<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\AbstractWhatever;

class KeepSelf extends AbstractWhatever
{
    /**
     * @var self
     */
    public $isValid = true;

    /**
     * @var parent
     */
    public $mama;

    /**
     * @var static
     */
    public $table;

    public function join(self $myself)
    {
    }
}

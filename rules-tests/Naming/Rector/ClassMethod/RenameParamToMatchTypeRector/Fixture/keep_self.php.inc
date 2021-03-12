<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\AbstractWhatever;

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

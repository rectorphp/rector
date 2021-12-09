<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Fixture;

use Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\Source\ParentWithParam;

class SkipUsedInCoalesce extends ParentWithParam{
    public function execute()
    {
        echo $foo ?? 'test';
    }
}

?>

<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class SkipFinalClass
{
    public function run()
    {
        echo 'first';
    }
}

?>

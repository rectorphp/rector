<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class SkipFinalClass
{
    public function run()
    {
        echo 'first';
    }
}

?>

<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class SkipUsedByExternal
{
    public function run()
    {
    }
}

class External
{
    public function go()
    {
        $obj = new SkipUsedByExternal();
        $obj->run();
    }
}

?>

<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

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

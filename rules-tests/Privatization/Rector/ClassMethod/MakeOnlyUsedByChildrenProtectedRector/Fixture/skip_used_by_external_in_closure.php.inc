<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class SkipUsedByExternalInClosure
{
    public function run()
    {
    }
}

function () {
    $obj = new SkipUsedByExternalInClosure();
    $obj->run();
};

?>

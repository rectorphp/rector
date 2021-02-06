<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

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

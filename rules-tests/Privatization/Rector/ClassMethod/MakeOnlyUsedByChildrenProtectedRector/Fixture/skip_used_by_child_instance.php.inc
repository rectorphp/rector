<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

abstract class SomeParent
{
    public function run()
    {
    }
}

final class ChildClass extends SomeParent
{
}

final class SkipUsedByChildInstance
{
     public function useIt(ChildClass $childClass)
     {
         // the method is used, so it should be skipped
         $childClass->run();
     }
}

?>

<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\Fixture;

class SkipExtended
{
    public function run()
    {
        echo 'first';
    }
}

class Foo extends SkipExtended
{
    public function run()
    {
        echo 'override';
    }
}

class Bar extends Foo
{
    public function go()
    {
        $this->run();
    }
}

?>

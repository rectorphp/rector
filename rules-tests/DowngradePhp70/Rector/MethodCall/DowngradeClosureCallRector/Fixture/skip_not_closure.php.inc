<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\Fixture;

class Foo 
{
    public function call($obj)
    {
        var_dump($obj);
    }
}

$foo = new Foo();
$foo->call($bar);

?>

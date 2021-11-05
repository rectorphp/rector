<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\Foo;

class RenameMethodCall
{
    private function call()
    {
        $foo = new Foo();
        $foo->old();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\Foo;

class RenameMethodCall
{
    private function call()
    {
        $foo = new Foo();
        $foo->new();
    }
}

?>

<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameClassMethod
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class RenameClassMethod
{
    public function __invoke()
    {
    }
}

?>

<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class SomePresenter
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class SomePresenter
{
    public function __invoke()
    {
    }
}

?>

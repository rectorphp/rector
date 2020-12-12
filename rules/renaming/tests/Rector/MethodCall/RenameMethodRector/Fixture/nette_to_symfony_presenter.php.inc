<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class NetteToSymfonyPresenter
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Fixture;

class NetteToSymfonyPresenter
{
    public function __invoke()
    {
    }
}

?>

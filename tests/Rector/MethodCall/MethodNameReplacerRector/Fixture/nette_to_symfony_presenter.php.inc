<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Fixture;

class SomePresenter
{
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector\Fixture;

class SomePresenter
{
    public function __invoke()
    {
    }
}

?>

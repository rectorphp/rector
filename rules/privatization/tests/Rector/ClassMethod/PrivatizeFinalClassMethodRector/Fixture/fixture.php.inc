<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

final class Fixture
{
    protected function someMethod()
    {
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

final class Fixture
{
    private function someMethod()
    {
    }
}

?>

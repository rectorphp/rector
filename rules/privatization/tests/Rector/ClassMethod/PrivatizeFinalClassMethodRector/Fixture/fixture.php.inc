<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

final class SomeClass
{
    protected function someMethod()
    {
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

final class SomeClass
{
    private function someMethod()
    {
    }
}

?>

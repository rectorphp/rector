<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\AbstractClassWithProtectedClassMethod;

final class ChangeParentWithoutCare extends AbstractClassWithProtectedClassMethod
{
    protected function getSome()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\AbstractClassWithProtectedClassMethod;

final class ChangeParentWithoutCare extends AbstractClassWithProtectedClassMethod
{
    private function getSome()
    {
    }
}

?>

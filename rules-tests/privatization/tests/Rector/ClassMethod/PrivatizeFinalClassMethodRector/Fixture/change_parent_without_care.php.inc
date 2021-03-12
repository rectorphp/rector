<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\AbstractClassWithProtectedClassMethod;

final class ChangeParentWithoutCare extends AbstractClassWithProtectedClassMethod
{
    protected function getSome()
    {
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Fixture;

use Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector\Source\AbstractClassWithProtectedClassMethod;

final class ChangeParentWithoutCare extends AbstractClassWithProtectedClassMethod
{
    private function getSome()
    {
    }
}

?>

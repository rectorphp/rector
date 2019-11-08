<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

class TestingStaticCall
{
    public static function process($name, $id)
    {
    }
}

class CallerClassForStaticCall
{
    public function run()
    {
        $building = new Apple();

        TestingStaticCall::process('hi', $building->getId());
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

class TestingStaticCall
{
    public static function process(string $name, \Ramsey\Uuid\UuidInterface $id)
    {
    }
}

class CallerClassForStaticCall
{
    public function run()
    {
        $building = new Apple();

        TestingStaticCall::process('hi', $building->getId());
    }
}

?>

<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

class InstantClassCall
{
    public function takeString($name)
    {
    }
}

class CallerClassInstantClassCall
{
    public function run(string $name)
    {
        (new InstantClassCall())->takeString($name);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

class InstantClassCall
{
    public function takeString(string $name)
    {
    }
}

class CallerClassInstantClassCall
{
    public function run(string $name)
    {
        (new InstantClassCall())->takeString($name);
    }
}

?>

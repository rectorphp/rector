<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

class Override
{
    public function process(bool $name, string $id)
    {
    }
}

class CallerClassForOverride
{
    public function run()
    {
        $someClass = new Override();
        $someClass->process('hi', false);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

class Override
{
    public function process(string $name, bool $id)
    {
    }
}

class CallerClassForOverride
{
    public function run()
    {
        $someClass = new Override();
        $someClass->process('hi', false);
    }
}

?>

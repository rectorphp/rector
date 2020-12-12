<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        return 'Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\AnotherClass';
    }

    public function preSlash()
    {
        return '\Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\AnotherClass';
    }
}

?>
-----
<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        return \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\AnotherClass::class;
    }

    public function preSlash()
    {
        return \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\AnotherClass::class;
    }
}

?>

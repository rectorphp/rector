<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class PreSlash
{
    public function run()
    {
        return '\Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture\AnotherClass';
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class PreSlash
{
    public function run()
    {
        return \Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture\AnotherClass::class;
    }
}

?>

<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class AnotherClass
{
}

class SomeClass
{
    public function run()
    {
        return 'Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture\AnotherClass';
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class AnotherClass
{
}

class SomeClass
{
    public function run()
    {
        return \Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture\AnotherClass::class;
    }
}

?>

<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Source\SomeClassWithAnyDefaultValue;

class ParamWithoutDefaultValue extends SomeClassWithAnyDefaultValue
{
    public function someMethod($argument)
    {

    }
}

class ParamWithScalar extends SomeClassWithAnyDefaultValue
{
    public function someMethod($argument = 1)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Source\SomeClassWithAnyDefaultValue;

class ParamWithoutDefaultValue extends SomeClassWithAnyDefaultValue
{
    public function someMethod($argument = [])
    {

    }
}

class ParamWithScalar extends SomeClassWithAnyDefaultValue
{
    public function someMethod($argument = [])
    {

    }
}

?>

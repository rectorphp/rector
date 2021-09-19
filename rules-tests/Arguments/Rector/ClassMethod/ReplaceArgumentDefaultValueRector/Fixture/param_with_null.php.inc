<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Source\SomeClassWithAnyDefaultValue;

class ParamWithNull extends SomeClassWithAnyDefaultValue
{
    public function paramWithNull($argument = null)
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector\Source\SomeClassWithAnyDefaultValue;

class ParamWithNull extends SomeClassWithAnyDefaultValue
{
    public function paramWithNull($argument = [])
    {

    }
}

?>

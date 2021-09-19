<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeClass;

class ArgumentWithoutType extends SomeClass
{
    public function withoutTypeOrDefaultValue($arguments = [])
    {
    }
}

class ArgumentWithoutDefaultValue extends SomeClass
{
    public function withoutTypeOrDefaultValue(array $arguments)
    {
    }
}

class ArgumentWithoutTypeAndDefaultValue extends SomeClass
{
    public function withoutTypeOrDefaultValue($arguments)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeClass;

class ArgumentWithoutType extends SomeClass
{
    public function withoutTypeOrDefaultValue(array $arguments = [])
    {
    }
}

class ArgumentWithoutDefaultValue extends SomeClass
{
    public function withoutTypeOrDefaultValue(array $arguments = [])
    {
    }
}

class ArgumentWithoutTypeAndDefaultValue extends SomeClass
{
    public function withoutTypeOrDefaultValue(array $arguments = [])
    {
    }
}

?>

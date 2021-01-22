<?php

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

class SomeObject
{
    public function run($required, $optional = 1)
    {
    }
}

?>

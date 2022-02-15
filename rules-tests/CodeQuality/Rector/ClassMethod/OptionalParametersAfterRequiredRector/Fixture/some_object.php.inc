<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

class SomeObject
{
    public function run($required, $optional = 1)
    {
    }
}

?>

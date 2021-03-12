<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

class Mashup
{
    public function run($optional = 1, $required, $anotherOptional = false, $yetRequired)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

class Mashup
{
    public function run($required, $yetRequired, $optional = 1, $anotherOptional = false)
    {
    }
}

?>

<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

trait SomeTrait
{
    public function execute()
    {
        $some_value = 5;

        $this->run($some_value);
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

trait SomeTrait
{
    public function execute()
    {
        $someValue = 5;

        $this->run($someValue);
    }
}

?>

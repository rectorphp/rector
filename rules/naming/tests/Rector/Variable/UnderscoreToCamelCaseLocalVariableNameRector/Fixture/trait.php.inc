<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

trait SomeTrait
{
    public function run($a_b)
    {
        $some_value = 5;

        $this->run($a_b);
    }

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
    public function run($a_b)
    {
        $someValue = 5;

        $this->run($a_b);
    }

    public function execute()
    {
        $someValue = 5;

        $this->run($someValue);
    }
}

?>

<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class Fixture
{
    public function run($a_b)
    {
        $some_value = 5;

        $this->run($a_b);
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\Fixture;

final class Fixture
{
    public function run($a_b)
    {
        $someValue = 5;

        $this->run($a_b);
    }
}

?>

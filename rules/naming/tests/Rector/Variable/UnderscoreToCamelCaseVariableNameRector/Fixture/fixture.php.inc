<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

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

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

final class Fixture
{
    public function run($aB)
    {
        $someValue = 5;

        $this->run($aB);
    }
}

?>

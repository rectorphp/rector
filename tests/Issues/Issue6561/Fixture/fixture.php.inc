<?php

namespace Rector\Core\Tests\Issues\Issue6561\Fixture;

final class Fixture
{
    public function run()
    {
        $array = [];

        if (count($array)) {
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6561\Fixture;

final class Fixture
{
    public function run()
    {
        $array = [];

        if ($array !== []) {
        }
    }
}

?>

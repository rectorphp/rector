<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadIfForeachForRector\Fixture;

class Fixture
{
    public function run($someObject)
    {
        $value = 5;
        if ($value) {
        }

        if ($someObject->run()) {
        }

        $values = [];
        foreach ($values as $value) {
        }

        return $value;
    }

    public function forMe()
    {
        for ($i = 0; $i < 5; ++$i) {
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadIfForeachForRector\Fixture;

class Fixture
{
    public function run($someObject)
    {
        $value = 5;

        if ($someObject->run()) {
        }

        $values = [];

        return $value;
    }

    public function forMe()
    {
    }
}

?>

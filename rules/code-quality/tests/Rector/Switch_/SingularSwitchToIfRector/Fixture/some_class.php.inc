<?php

namespace Rector\CodeQuality\Tests\Rector\Switch_\SingularSwitchToIfRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        $result = 1;
        switch ($value) {
            case 100:
            $result = 1000;
        }

        return $result;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Switch_\SingularSwitchToIfRector\Fixture;

class SomeClass
{
    public function run($value)
    {
        $result = 1;
        if ($value === 100) {
            $result = 1000;
        }

        return $result;
    }
}

?>

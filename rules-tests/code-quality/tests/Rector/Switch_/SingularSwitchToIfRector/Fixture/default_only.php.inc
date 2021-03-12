<?php

namespace Rector\CodeQuality\Tests\Rector\Switch_\SingularSwitchToIfRector\Fixture;

final class DefaultOnly
{
    public function run($value)
    {
        $result = 1;
        switch ($value) {
            default:
                $result = 1000;
        }

        return $result;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Switch_\SingularSwitchToIfRector\Fixture;

final class DefaultOnly
{
    public function run($value)
    {
        $result = 1;
        $result = 1000;

        return $result;
    }
}

?>

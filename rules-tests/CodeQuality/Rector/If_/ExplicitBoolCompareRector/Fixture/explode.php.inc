<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

class SomeExplode
{
    public function run()
    {
        $values = explode('_', 'a_0_b');
        foreach ($values as $value) {
            if ($value) {
                echo $value . PHP_EOL;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

class SomeExplode
{
    public function run()
    {
        $values = explode('_', 'a_0_b');
        foreach ($values as $value) {
            if ($value !== '' && $value !== '0') {
                echo $value . PHP_EOL;
            }
        }
    }
}

?>

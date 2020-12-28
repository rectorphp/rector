<?php

namespace Rector\CodeQuality\Tests\Rector\If_\CombineIfRector\Fixture;

class Fixture
{
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\CombineIfRector\Fixture;

class Fixture
{
    public function run()
    {
        if ($cond1 && $cond2) {
            return 'foo';
        }
    }
}

?>

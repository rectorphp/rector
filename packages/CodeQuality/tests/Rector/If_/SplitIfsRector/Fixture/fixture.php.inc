<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\Fixture;

class SomeClass {
    public function run()
    {
        if ($cond1) {
            return 'foo';
        } else {
            foo();
            return 'bar';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\Fixture;

class SomeClass {
    public function run()
    {
        if ($cond1) {
            return 'foo';
        }
        foo();
        return 'bar';
    }
}

?>

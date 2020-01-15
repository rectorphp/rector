<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\Throw_;

class SomeClass {
    public function run()
    {
        if ($cond1) {
            throw new \Exception('should not happen');
        } else {
            foo();
            return 'bar';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\Throw_;

class SomeClass {
    public function run()
    {
        if ($cond1) {
            throw new \Exception('should not happen');
        }
        foo();
        return 'bar';
    }
}

?>

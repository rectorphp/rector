<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\Exit_;

class SomeClass {
    public function run()
    {
        if ($cond1) {
            exit('bye');
        } else {
            foo();
            return 'bar';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector\Exit_;

class SomeClass {
    public function run()
    {
        if ($cond1) {
            exit('bye');
        }
        foo();
        return 'bar';
    }
}

?>

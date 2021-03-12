<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

class DoNotRemoveParentChildFlip
{
    public function go(Node $var)
    {
        if (! $var instanceof If_) {
            return false;
        }
        return true;
    }

    public function run(If_ $var)
    {
        if (! $var instanceof Node) {
            return false;
        }
        return true;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

class DoNotRemoveParentChildFlip
{
    public function go(Node $var)
    {
        if (! $var instanceof If_) {
            return false;
        }
        return true;
    }

    public function run(If_ $var)
    {
        return true;
    }
}

?>

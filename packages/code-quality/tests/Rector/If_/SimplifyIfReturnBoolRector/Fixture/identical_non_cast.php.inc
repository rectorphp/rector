<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PhpParser\Node\Stmt\If_;

class IdenticalNonCast
{
    public function run(If_ $if)
    {
        if ($if->else === null) {
            return false;
        }

        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PhpParser\Node\Stmt\If_;

class IdenticalNonCast
{
    public function run(If_ $if)
    {
        return $if->else !== null;
    }
}

?>

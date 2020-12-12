<?php

namespace Rector\CodeQuality\Tests\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\Fixture;

class Fixture
{
    public function run()
    {
        $tokens = [];
        $token = 4 and $tokens[] = $token;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\Fixture;

class Fixture
{
    public function run()
    {
        $tokens = [];
        $token = 4;
        $tokens[] = $token;
    }
}

?>

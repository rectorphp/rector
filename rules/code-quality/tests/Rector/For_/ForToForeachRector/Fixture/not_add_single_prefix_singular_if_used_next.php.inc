<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class NotAddSinglePrefixSingularIfUsedNext
{
    public function run($token, $singleToken)
    {
        if (rand(0, 1)) {
            for ($i = 0; $i < count($token); $i++) {
            }
        }

        echo $singleToken;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class NotAddSinglePrefixSingularIfUsedNext
{
    public function run($token, $singleToken)
    {
        if (rand(0, 1)) {
            foreach ($token as $i => $token) {
            }
        }

        echo $singleToken;
    }
}

?>

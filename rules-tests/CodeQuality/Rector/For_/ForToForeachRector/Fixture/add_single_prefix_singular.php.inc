<?php

namespace Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\Fixture;

class AddSinglePrefixSingular
{
    public function run($token)
    {
        for ($i = 0; $i < count($token); $i++) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\Fixture;

class AddSinglePrefixSingular
{
    public function run($token)
    {
        foreach ($token as $i => $singleToken) {
        }
    }
}

?>

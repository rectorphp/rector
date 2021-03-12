<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class ForWithSwitchedCompare
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $c > $i; $i++) {
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class ForWithSwitchedCompare
{
    public function run($tokens)
    {
        foreach ($tokens as $i => $token) {
        }
    }
}

?>

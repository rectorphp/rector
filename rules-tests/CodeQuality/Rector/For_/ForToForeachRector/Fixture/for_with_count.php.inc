<?php

namespace Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\Fixture;

class ForWithCount
{
    public function run($tokens)
    {
        for ($i = 0; $i < count($tokens); $i++) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\Fixture;

class ForWithCount
{
    public function run($tokens)
    {
        foreach ($tokens as $i => $token) {
        }
    }
}

?>

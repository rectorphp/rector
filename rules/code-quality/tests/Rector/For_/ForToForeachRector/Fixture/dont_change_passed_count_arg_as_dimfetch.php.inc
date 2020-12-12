<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class DontChangePassedCountArgAsDimfetch
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; $i++) {
            for ($i2 = 0, $c2 = count($tokens[$i]); $i2 < $c2; $i2++) {
                var_dump($tokens[$i]);
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class DontChangePassedCountArgAsDimfetch
{
    public function run($tokens)
    {
        foreach ($tokens as $i => $token) {
            for ($i2 = 0, $c2 = count($tokens[$i]); $i2 < $c2; $i2++) {
                var_dump($token);
            }
        }
    }
}

?>
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class MirrorComment
{
    public function run($tokens)
    {
        // a comment
        for ($i = 0; $i < count($tokens); $i++) {
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class MirrorComment
{
    public function run($tokens)
    {
        // a comment
        foreach ($tokens as $i => $token) {
        }
    }
}

?>

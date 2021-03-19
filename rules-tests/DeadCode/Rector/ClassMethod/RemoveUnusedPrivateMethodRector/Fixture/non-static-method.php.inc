<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

class NonStaticMethod
{
    private function notcalled() {
        $x = 3;
        if ($x > 2) {
            echo "yes";
        }
        return "no";
    }

    public function run()
    {
        echo "hidiho";
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

class NonStaticMethod
{
    public function run()
    {
        echo "hidiho";
    }
}

?>

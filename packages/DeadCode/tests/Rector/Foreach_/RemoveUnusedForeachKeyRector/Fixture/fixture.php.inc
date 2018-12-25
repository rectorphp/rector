<?php

namespace Rector\DeadCode\Tests\Rector\Foreach_\RemoveUnusedForeachKeyRector\Fixture;

class RemoveUnusedForeachKey
{
    public function run()
    {
        $items = [];
        foreach ($items as $key => $value) {
            $result = $value;
        }

        foreach ($items as $key => $value) {
            $result = $key;
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Foreach_\RemoveUnusedForeachKeyRector\Fixture;

class RemoveUnusedForeachKey
{
    public function run()
    {
        $items = [];
        foreach ($items as $value) {
            $result = $value;
        }

        foreach ($items as $key => $value) {
            $result = $key;
        }
    }
}

?>

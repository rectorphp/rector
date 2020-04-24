<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class SomeClass
{
    public function run()
    {
        $items = [];
        foreach ($values as $key => $value) {
            $items[$key] = null;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class SomeClass
{
    public function run()
    {
        $items = [];
        foreach (array_keys($values) as $key) {
            $items[$key] = null;
        }
    }
}

?>

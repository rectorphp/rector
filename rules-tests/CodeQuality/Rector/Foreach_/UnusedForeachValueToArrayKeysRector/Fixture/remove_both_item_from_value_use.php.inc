<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class RemoveBothItemFromValueUse
{
    public function run(array $definitions)
    {
        foreach ($definitions as $id => [$domElement, $file]) {
            echo $id;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class RemoveBothItemFromValueUse
{
    public function run(array $definitions)
    {
        foreach (array_keys($definitions) as $id) {
            echo $id;
        }
    }
}

?>

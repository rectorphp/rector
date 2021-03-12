<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class RemoveSingleItemFromValueUse
{
    public function run(array $definitions)
    {
        foreach ($definitions as $id => [$domElement, $file]) {
            if ($domElement) {
                return true;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

class RemoveSingleItemFromValueUse
{
    public function run(array $definitions)
    {
        foreach ($definitions as $id => [$domElement]) {
            if ($domElement) {
                return true;
            }
        }
    }
}

?>

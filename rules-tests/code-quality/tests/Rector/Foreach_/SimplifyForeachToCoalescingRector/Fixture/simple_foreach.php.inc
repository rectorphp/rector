<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector\Fixture;

final class SimpleForeach
{
    public function run(array $oldToNewFunctions, $currentFunction)
    {
        foreach ($oldToNewFunctions as $oldFunction => $newFunction) {
            if ($currentFunction === $oldFunction) {
                return $newFunction;
            }
        }

        return 45;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector\Fixture;

final class SimpleForeach
{
    public function run(array $oldToNewFunctions, $currentFunction)
    {
        return $oldToNewFunctions[$currentFunction] ?? 45;
    }
}

?>

<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipArrayDimFetchNext
{
   private function createArgsFromItems(array $items, ?string $silentKey = null): array
    {
        $args = [];

        if ($silentKey !== null && isset($items[$silentKey])) {
            $args[] = 1;
        }

        if (true) {
            foreach ($items as $key => $value) {
                $args[] = 2;
            }
        } else {
            foreach ($items as $value) {
                $args[] = 3;
            }
        }
        return $args;
    }
}

?>

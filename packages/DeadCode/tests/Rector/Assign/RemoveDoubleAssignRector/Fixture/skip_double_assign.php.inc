<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class SkipDoubleAssign
{
    public function run($alreadyImportedUses = [], $attributeAwareNode, $shortName)
    {
        foreach ($alreadyImportedUses as $differentValue) {
            if ($differentValue === 10) {
                $attributeAwareNode->name = $shortName;
            }

            return $attributeAwareNode;
        }

        $attributeAwareNode->name = $shortName;
    }
}

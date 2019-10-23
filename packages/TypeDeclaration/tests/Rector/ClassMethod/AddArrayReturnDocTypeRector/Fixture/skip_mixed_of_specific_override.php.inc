<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipMixedOfSpecificOverride
{
    /**
     * @return string[]
     */
    public function provideForVendor(array $items): array
    {
        // exclude undesired packages
        return array_diff($items, []);
    }
}

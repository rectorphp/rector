<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class SkipMultiUnionTypes
{
    /**
     * @param int|int[]|null|string $id
     */
    public function get($id)
    {
        return empty($id);
    }
}

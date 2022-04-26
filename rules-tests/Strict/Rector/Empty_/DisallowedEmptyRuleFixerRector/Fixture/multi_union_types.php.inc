<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class MultiUnionTypes
{
    /**
     * @param int|int[]|null|string $id
     */
    public function get($id)
    {
        return empty($id);
    }
}

?>
-----
<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class MultiUnionTypes
{
    /**
     * @param int|int[]|null|string $id
     */
    public function get($id)
    {
        return $id === 0 || $id === '' || $id === [] || $id === null;
    }
}

?>

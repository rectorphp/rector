<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class SomeEmptyArray
{
    public function run(array $items)
    {
        return empty($items);
    }
}

?>
-----
<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class SomeEmptyArray
{
    public function run(array $items)
    {
        return $items === [];
    }
}

?>

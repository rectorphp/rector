<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class UnionObjectNullable
{
    public function run(null|\DateTime $dateTime)
    {
        return empty($dateTime);
    }
}

?>
-----
<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class UnionObjectNullable
{
    public function run(null|\DateTime $dateTime)
    {
        return $dateTime === null;
    }
}

?>

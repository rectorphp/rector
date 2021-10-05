<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class EmptyStringNullable
{
    public function run(string|null $value)
    {
        return empty($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

final class EmptyStringNullable
{
    public function run(string|null $value)
    {
        return $value === null || $value === '';
    }
}

?>

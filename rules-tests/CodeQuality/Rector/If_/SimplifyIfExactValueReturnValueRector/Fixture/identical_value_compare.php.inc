<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfExactValueReturnValueRector\Fixture;

final class IdentialValueCompare
{
    public function run($value)
    {
        if ($value === 93) {
            return 93;
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfExactValueReturnValueRector\Fixture;

final class IdentialValueCompare
{
    public function run($value)
    {
        return $value;
    }
}

?>

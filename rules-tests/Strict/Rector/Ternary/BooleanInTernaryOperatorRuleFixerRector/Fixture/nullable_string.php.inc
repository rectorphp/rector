<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class NullableString
{
    public function run(string|null $value)
    {
        return $value ? 1 : 2;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Strict\Rector\Ternary\BooleanInTernaryOperatorRuleFixerRector\Fixture;

final class NullableString
{
    public function run(string|null $value)
    {
        return $value !== null && $value !== '' ? 1 : 2;
    }
}

?>

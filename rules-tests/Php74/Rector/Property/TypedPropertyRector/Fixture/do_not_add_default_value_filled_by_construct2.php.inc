<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotAddDefaultValueFilledByConstruct2
{
    private static $result;

    public function __construct(?array $result) {
        self::$result = $result;
    }

    public function getResult(): ?array
    {
        return self::$result;
    }
}
?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotAddDefaultValueFilledByConstruct2
{
    private static ?array $result;

    public function __construct(?array $result) {
        self::$result = $result;
    }

    public function getResult(): ?array
    {
        return self::$result;
    }
}
?>

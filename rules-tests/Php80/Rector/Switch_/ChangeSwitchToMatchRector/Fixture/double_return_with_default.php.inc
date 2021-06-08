<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DoubleReturnWithDefault
{
    public static function toStorageValue($input): string
    {
        switch ($input) {
            case 100:
                return 'yes';
            case 200:
                return 'no';
            default:
                throw new \RuntimeException;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class DoubleReturnWithDefault
{
    public static function toStorageValue($input): string
    {
        return match ($input) {
            100 => 'yes',
            200 => 'no',
            default => throw new \RuntimeException,
        };
    }
}

?>

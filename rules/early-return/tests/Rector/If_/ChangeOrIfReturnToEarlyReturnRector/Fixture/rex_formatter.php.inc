<?php
namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

abstract class rex_formatter
{
    private static function getTimestamp($value)
    {
        if (is_int($value) || ctype_digit($value)) {
            return (int) $value;
        }

        return strtotime($value);
    }
}
?>
-----
<?php
namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

abstract class rex_formatter
{
    private static function getTimestamp($value)
    {
        if (is_int($value)) {
            return $value;
        }
        if (ctype_digit($value)) {
            return (int) $value;
        }
        return strtotime($value);
    }
}
?>

<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

final class InClosure
{
    public function run($value)
    {
        return (static function (mixed $default) : mixed {
            $default = self::getValue($default);
            return match (true) {
                is_string($default) => sprintf('"%s"', $default),
                default => $default,
            };
        })($value);
    }

    private static function getValue($default) {return $default;}
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

final class InClosure
{
    public function run($value)
    {
        return (static function (mixed $default) : mixed {
            $default = self::getValue($default);
            switch (true) {
                case is_string($default):
                    return sprintf('"%s"', $default);
                default:
                    return $default;
            }
        })($value);
    }

    private static function getValue($default) {return $default;}
}

?>

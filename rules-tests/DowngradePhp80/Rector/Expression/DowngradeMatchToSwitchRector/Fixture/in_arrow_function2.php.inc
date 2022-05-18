<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

final class InArrowFunction2
{
    public function run($value)
    {
        $mapped = static fn (mixed $default): mixed => match (true) {
                is_string($default) => sprintf('"%s"', $default),
                default => $default,
            };

        return $mapped($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\Fixture;

final class InArrowFunction2
{
    public function run($value)
    {
        $mapped = static function (mixed $default) : mixed {
            switch (true) {
                case is_string($default):
                    return sprintf('"%s"', $default);
                default:
                    return $default;
            }
        };

        return $mapped($value);
    }
}

?>

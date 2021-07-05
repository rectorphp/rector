<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class StaticCall
{
    public function run(array $values)
    {
        usort($values, [StaticCall::class, 'compareSize']);

        return $values;
    }

    private static function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class StaticCall
{
    public function run(array $values)
    {
        usort($values, function (int $first, $second) : bool {
            return StaticCall::compareSize($first, $second);
        });

        return $values;
    }

    private static function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}

?>

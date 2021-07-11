<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class SomeStaticCall
{
    public function run(array $values)
    {
        usort($values, [SomeStaticCall::class, 'compareSize']);

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

final class SomeStaticCall
{
    public function run(array $values)
    {
        usort($values, function (int $first, $second) : bool {
            return SomeStaticCall::compareSize($first, $second);
        });

        return $values;
    }

    private static function compareSize(int $first, $second): bool
    {
        return $first <=> $second;
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class PreserveByRefClosureUse
{

    public function doFoo(
        int $j
    ): void
    {
        $nameScopeMap = [];
        $f = function () use ($j, &$nameScopeMap): ?int {
            $nameScopeMap[] = function () use ($j): callable {
                return static fn (int $i): int => $i + $j;
            };

            return null;
        };
        $f();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class PreserveByRefClosureUse
{

    public function doFoo(
        int $j
    ): void
    {
        $nameScopeMap = [];
        $f = function () use ($j, &$nameScopeMap): ?int {
            $nameScopeMap[] = function () use ($j): callable {
                return static function (int $i) use ($j) : int {
                    return $i + $j;
                };
            };

            return null;
        };
        $f();
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

final class NestedStaticFnUses
{
    public function __invoke()
    {
        return fn ($foo) => fn ($bar) => fn () => [$foo, $bar];
    }

    public function many()
    {
        return fn ($foo) => fn ($bar) => fn ($baz) => fn () => [$foo, $bar, $baz];
    }

    public function toomany()
    {
        return fn ($foo) => fn ($bar) => fn ($baz) => fn ($bat) => fn () => [$foo, $bar, $baz, $bat];
    }

    public function mixed()
    {
        return fn($foo) => fn ($bar) => function () use ($foo, $bar) {
            return [$foo, $bar];
        };
    }
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

final class NestedStaticFnUses
{
    public function __invoke()
    {
        return function ($foo) {
            return function ($bar) use ($foo) {
                return function () use ($foo, $bar) {
                    return [$foo, $bar];
                };
            };
        };
    }

    public function many()
    {
        return function ($foo) {
            return function ($bar) use ($foo) {
                return function ($baz) use ($foo, $bar) {
                    return function () use ($foo, $bar, $baz) {
                        return [$foo, $bar, $baz];
                    };
                };
            };
        };
    }

    public function toomany()
    {
        return function ($foo) {
            return function ($bar) use ($foo) {
                return function ($baz) use ($foo, $bar) {
                    return function ($bat) use ($foo, $bar, $baz) {
                        return function () use ($foo, $bar, $baz, $bat) {
                            return [$foo, $bar, $baz, $bat];
                        };
                    };
                };
            };
        };
    }

    public function mixed()
    {
        return function ($foo) {
            return function ($bar) use ($foo) {
                return function () use ($foo, $bar) {
                    return [$foo, $bar];
                };
            };
        };
    }
}
?>

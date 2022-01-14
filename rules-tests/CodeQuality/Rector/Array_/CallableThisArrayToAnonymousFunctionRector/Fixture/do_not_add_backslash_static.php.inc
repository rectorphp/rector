<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class DoNotAddBackslashStatic
{
    public function run(callable $transform)
    {
        $transform();
    }

    public function run2()
    {
        $this->run([static::class, 'test']);
    }

    public static function test(): void
    {
        echo 'test';
    }
}
?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class DoNotAddBackslashStatic
{
    public function run(callable $transform)
    {
        $transform();
    }

    public function run2()
    {
        $this->run(function () : void {
            static::test();
        });
    }

    public static function test(): void
    {
        echo 'test';
    }
}
?>

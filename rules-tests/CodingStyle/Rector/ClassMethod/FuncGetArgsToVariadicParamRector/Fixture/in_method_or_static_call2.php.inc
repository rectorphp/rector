<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

use Webmozart\Assert\Assert;

final class InMethodOrStaticCall2
{
    public function run(): void
    {
        function () {
            $this->count($args = func_get_args(), 1);
            Assert::count($args = func_get_args(), 1);
        };
    }

    public function run2(): void
    {
        function innerFunc() {
            Assert::count($args = func_get_args(), 1);
        };
    }

    private function count($args, $value)
    {
        Assert::count($args, $value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

use Webmozart\Assert\Assert;

final class InMethodOrStaticCall2
{
    public function run(): void
    {
        function (...$args) {
            $this->count($args, 1);
            Assert::count($args, 1);
        };
    }

    public function run2(): void
    {
        function innerFunc(...$args) {
            Assert::count($args, 1);
        };
    }

    private function count($args, $value)
    {
        Assert::count($args, $value);
    }
}

?>

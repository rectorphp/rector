<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector\Fixture;

use Webmozart\Assert\Assert;

final class InMethodOrStaticCall
{
    public function run(): void
    {
        $this->count($args = func_get_args(), 1);
        Assert::count($args = func_get_args(), 1);
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

final class InMethodOrStaticCall
{
    public function run(...$args): void
    {
        $this->count($args, 1);
        Assert::count($args, 1);
    }

    private function count($args, $value)
    {
        Assert::count($args, $value);
    }
}

?>

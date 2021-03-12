<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

final class UndefinedArray
{
    public function run()
    {
        $a['a'] = 'b';
        $a['a'] = 'b';
        $a['a'] = 'b';
    }
}
?>
-----
<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

final class UndefinedArray
{
    public function run()
    {
        $a = [];
        $a['a'] = 'b';
        $a['a'] = 'b';
        $a['a'] = 'b';
    }
}
?>

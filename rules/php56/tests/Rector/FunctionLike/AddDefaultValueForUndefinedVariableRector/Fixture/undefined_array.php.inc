<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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

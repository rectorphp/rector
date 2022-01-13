<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class PassNullMulti
{
    public function run()
    {
        str_contains(null, null);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class PassNullMulti
{
    public function run()
    {
        str_contains('', '');
    }
}

?>

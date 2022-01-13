<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class PassNull
{
    public function run()
    {
        preg_split('#a#', null);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class PassNull
{
    public function run()
    {
        preg_split('#a#', '');
    }
}

?>

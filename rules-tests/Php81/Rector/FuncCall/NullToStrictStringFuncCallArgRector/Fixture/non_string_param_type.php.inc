<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class NonStringParamType
{
    public function run($subject)
    {
        preg_split('#a#', $subject);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class NonStringParamType
{
    public function run($subject)
    {
        preg_split('#a#', (string) $subject);
    }
}

?>

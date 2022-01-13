<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class NonStringParamTypeInUseClosure
{
    public function run($subject)
    {
        function () use ($subject) {
            preg_split('#a#', $subject);
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class NonStringParamTypeInUseClosure
{
    public function run($subject)
    {
        function () use ($subject) {
            preg_split('#a#', (string) $subject);
        };
    }
}

?>

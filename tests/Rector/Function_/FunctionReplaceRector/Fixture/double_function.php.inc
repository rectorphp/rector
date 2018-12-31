<?php

namespace Rector\Tests\Rector\Function_\FunctionReplaceRector\Fixture;

class DoubleFunction
{
    public function run($text)
    {
        hebrevc($text);
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Function_\FunctionReplaceRector\Fixture;

class DoubleFunction
{
    public function run($text)
    {
        \nl2br(\hebrev($text));
    }
}

?>

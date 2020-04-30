<?php

namespace Rector\Renaming\Tests\Rector\Function_\RenameFunctionRector\Fixture;

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

namespace Rector\Renaming\Tests\Rector\Function_\RenameFunctionRector\Fixture;

class DoubleFunction
{
    public function run($text)
    {
        \nl2br(\hebrev($text));
    }
}

?>

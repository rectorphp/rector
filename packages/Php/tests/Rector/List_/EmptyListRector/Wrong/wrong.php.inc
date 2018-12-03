<?php

namespace Rector\Php\Tests\Rector\List_\EmptyListRector\Wrong;

class EmptyList
{
    private function run()
    {
        $values = [];
        list() = $values;

        list(, ,) = $a;

        list($x, list(), $y) = $a;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\List_\EmptyListRector\Wrong;

class EmptyList
{
    private function run()
    {
        $values = [];
        list($unusedGenerated) = $values;

        list($unusedGenerated, ,) = $a;

        list($x, list($unusedGenerated), $y) = $a;
    }
}

?>

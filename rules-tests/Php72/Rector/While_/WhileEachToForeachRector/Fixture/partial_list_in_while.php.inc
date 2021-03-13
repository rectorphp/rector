<?php

namespace Rector\Tests\Php72\Rector\While_\WhileEachToForeachRector\Fixture;

final class PartialListInWhile
{
    public function run()
    {
        $iteratedLines = [];
        $lines = ['a', 'b'];

        while (list(, $line) = each($lines)) {
            $iteratedLines[] = $line;
        }

        return $iteratedLines;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\While_\WhileEachToForeachRector\Fixture;

final class PartialListInWhile
{
    public function run()
    {
        $iteratedLines = [];
        $lines = ['a', 'b'];

        foreach ($lines as $line) {
            $iteratedLines[] = $line;
        }

        return $iteratedLines;
    }
}

?>

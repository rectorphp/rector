<?php

namespace Rector\Tests\Php72\Rector\While_\WhileEachToForeachRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class PartialListInWhile implements RunnableInterface
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

use Rector\Testing\Contract\RunnableInterface;

final class PartialListInWhile implements RunnableInterface
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

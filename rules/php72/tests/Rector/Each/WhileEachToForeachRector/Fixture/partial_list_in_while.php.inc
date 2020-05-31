<?php

namespace Rector\Php72\Tests\Rector\Each\WhileEachToForeachRector\Fixture;

use Rector\Core\Testing\Contract\RunnableInterface;

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

namespace Rector\Php72\Tests\Rector\Each\WhileEachToForeachRector\Fixture;

use Rector\Core\Testing\Contract\RunnableInterface;

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

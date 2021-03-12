<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class CommentInsideIfStatement
{
    public function run($values)
    {
        $items = [];

        foreach ($values as $value) {
            if ($value === 5) // why am I doing this?
            {
                if ($value === 10) {
                    $items[] = 'maybe';
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class CommentInsideIfStatement
{
    public function run($values)
    {
        $items = [];

        foreach ($values as $value) {
            if ($value !== 5) {
                continue;
            }
            // why am I doing this?
            if ($value !== 10) {
                continue;
            }
            $items[] = 'maybe';
        }
    }
}

?>

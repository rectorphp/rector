<?php

namespace Rector\SOLID\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class CommentInsideIfStatement
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value === 5) //why am I doing this?
            {
                if ($value2 === 10) {
                    $items[] = 'maybe';
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class CommentInsideIfStatement
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value !== 5) {
                //why am I doing this?
                continue;
            }
            if ($value2 !== 10) {
                continue;
            }
            $items[] = 'maybe';
        }
    }
}

?>

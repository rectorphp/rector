<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class DoNotRemoveCommentIfAssignmentNotRemoved
{
    protected function run(array $data)
    {
        foreach ($data as $key => $val) {

            if (rand(0, 1)) {
                // a comment
                $val = str_replace('a', 'b', $val);
                $val = str_replace('b', 'c', $val);
            }

            if (rand(0, 1)) {
                if (rand(0, 1)) {
                    // a comment
                    $val = str_replace('a', 'b', $val);
                    $val = str_replace('b', 'c', $val);
                }
            }
        }

        return false;
    }
}
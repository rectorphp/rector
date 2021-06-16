<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class KeepTheElseIfElseifHasNoStmt
{
    public function convert($data)
    {
        // a comment 1
        if (is_array($data)) {
            return $res;
        }
        // a comment 2
        elseif ($data instanceof EncodedString) {
        }
        // a comment 3
        elseif (is_string($data)) {
        } else {
            return $data;
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class KeepTheElseIfElseifHasNoStmt
{
    public function convert($data)
    {
        // a comment 1
        if (is_array($data)) {
            return $res;
        }
        // a comment 2
        if ($data instanceof EncodedString) {
        }
        // a comment 3
        elseif (is_string($data)) {
        }
        else {
            return $data;
        }
    }
}

?>

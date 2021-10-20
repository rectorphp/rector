<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\Fixture;

class LostCommentBeforeElseif2
{
    public function convert($data)
    {
        if (is_array($data)) {
            return $res;
        } elseif ($data instanceof EncodedString) {
        }
        // this comment should be kept
        elseif (is_string($data)) {
            return $data;
        }
        return $data;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\Fixture;

class LostCommentBeforeElseif2
{
    public function convert($data)
    {
        if (is_array($data)) {
            return $res;
        }
        if ($data instanceof EncodedString) {
        }
        // this comment should be kept
        elseif (is_string($data)) {
            return $data;
        }
        return $data;
    }
}

?>

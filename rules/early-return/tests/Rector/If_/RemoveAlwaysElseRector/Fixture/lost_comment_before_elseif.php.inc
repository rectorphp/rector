<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class LostCommentBeforeElseif
{
    public function convert($data)
    {
        if (is_array($data)) {
            $res = [];
            foreach ($data as $key => $value) {
                $res[$this->convert($key)] = $this->convert($value);
            }

            return $res;
        }
        // because ASCII is a subset of all charset
        elseif (is_string($data) && !empty($data) && !is_numeric($data) && 'ASCII' !== mb_detect_encoding($data)) {
            $data = $this->convertString($data);
        }

        return $data;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class LostCommentBeforeElseif
{
    public function convert($data)
    {
        if (is_array($data)) {
            $res = [];
            foreach ($data as $key => $value) {
                $res[$this->convert($key)] = $this->convert($value);
            }
            return $res;
        }
        // because ASCII is a subset of all charset
        if (is_string($data) && !empty($data) && !is_numeric($data) && 'ASCII' !== mb_detect_encoding($data)) {
            $data = $this->convertString($data);
        }

        return $data;
    }
}

?>

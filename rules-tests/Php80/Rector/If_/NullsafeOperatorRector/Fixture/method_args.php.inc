<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class MethodArgs
{
    public function run()
    {
        if ($this->endWeekDate !== null) {
            $param = $this->endWeekDate->format('Y-m-d');
        } else {
            $param = null;
        }
        return $param;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class MethodArgs
{
    public function run()
    {
        $param = $this->endWeekDate?->format('Y-m-d');
        return $param;
    }
}

?>

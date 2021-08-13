<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class SkipDefaultValueMoreStmt
{
    public function run()
    {
        if ($this->endWeekDate !== null) {
            $param = $this->endWeekDate->format();
        } else {
            $param = "No data";

            echo 'STATEMENT HERE';
        }
        return $param;
    }
}

?>

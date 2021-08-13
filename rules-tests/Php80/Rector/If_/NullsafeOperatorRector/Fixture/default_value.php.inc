<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class DefaultValue
{
    public function run()
    {
        if ($this->endWeekDate !== null) {
            $param = $this->endWeekDate->format();
        } else {
            $param = "No data";
        }
        return $param;
    }
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

final class DefaultValue
{
    public function run()
    {
        $param = $this->endWeekDate?->format() ?? "No data";
        return $param;
    }
}
?>

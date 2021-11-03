<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class BoolVarIfCondReturnTrueNextReturnBoolVar
{
    public function run(bool $value)
    {
        if ($value) {
            return true;
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class BoolVarIfCondReturnTrueNextReturnBoolVar
{
    public function run(bool $value)
    {
        return $value;
    }
}

?>

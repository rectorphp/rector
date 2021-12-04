<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class FromTypedParam
{
    public function run(?int $id)
    {
        return $id !== null && $id !== 'null';
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\Fixture;

class FromTypedParam
{
    public function run(?int $id)
    {
        return $id !== null && $id !== 0;
    }
}

?>

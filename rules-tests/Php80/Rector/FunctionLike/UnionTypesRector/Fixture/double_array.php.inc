<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class DoubleArray
{
    /**
     * @param string[]|bool[]|int $number
     */
    public function go($number)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class DoubleArray
{
    /**
     * @param string[]|bool[]|int $number
     */
    public function go(array|int $number)
    {
    }
}

?>

<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class Fixture
{
    /**
     * @param array|int $number
     * @return bool|float
     */
    public function go($number)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class Fixture
{
    public function go(array|int $number): bool|float
    {
    }
}

?>

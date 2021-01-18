<?php

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

class Fixture
{
    public function go(array|int $number): bool|float
    {
    }
}

?>

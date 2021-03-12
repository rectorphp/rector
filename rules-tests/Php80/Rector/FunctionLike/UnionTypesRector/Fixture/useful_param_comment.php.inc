<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class UsefulParamComment
{
    /**
     * @param array|int $number some comment
     */
    public function go($number)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class UsefulParamComment
{
    /**
     * @param array|int $number some comment
     */
    public function go(array|int $number)
    {
    }
}

?>

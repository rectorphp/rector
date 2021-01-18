<?php

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

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

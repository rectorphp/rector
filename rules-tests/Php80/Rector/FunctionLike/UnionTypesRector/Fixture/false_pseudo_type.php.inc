<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class FalsePseudoType
{
    /**
     * @return false|int
     */
    public function go($value)
    {
        return (int) $value ?? false;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class FalsePseudoType
{
    public function go($value): int|false
    {
        return (int) $value ?? false;
    }
}

?>

<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class FalsePseudoType
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

class FalsePseudoType
{
    public function go($value): false|int
    {
        return (int) $value ?? false;
    }
}

?>

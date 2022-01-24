<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class TruePseudoType
{
    /**
     * @return true|int
     */
    public function go($value)
    {
        return (int) $value ?? true;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class TruePseudoType
{
    /**
     * @return true|int
     */
    public function go($value): int|bool
    {
        return (int) $value ?? true;
    }
}

?>

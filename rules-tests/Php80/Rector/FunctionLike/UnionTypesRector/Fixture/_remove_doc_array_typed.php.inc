<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class RemoveDocArrayTyped
{
    /**
     * @return bool|float|int|string|array<mixed>
     */
    public function normalizeNodeValue($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class RemoveDocArrayTyped
{
    public function normalizeNodeValue($value): bool|float|int|string|array
    {
        return $value;
    }
}

?>

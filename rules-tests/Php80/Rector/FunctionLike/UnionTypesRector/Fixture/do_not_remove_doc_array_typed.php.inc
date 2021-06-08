<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class DoNotRemoveDocArrayTyped
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

class DoNotRemoveDocArrayTyped
{
    /**
     * @return array<mixed>
     */
    public function normalizeNodeValue($value): bool|float|int|string|array
    {
        return $value;
    }
}

?>

<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\Fixture;

final class Fixture
{
    public function isEmptyArray($values): bool
    {
        return is_array($values) && empty($values);
    }

    public function isEmptyArray2($values): bool
    {
        return empty($values) && is_array($values);
    }

    public function functionCallInsideEmpty($values): bool
    {
        return is_array($values) && empty(array_filter($values));
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\Fixture;

final class Fixture
{
    public function isEmptyArray($values): bool
    {
        return $values === [];
    }

    public function isEmptyArray2($values): bool
    {
        return $values === [];
    }

    public function functionCallInsideEmpty($values): bool
    {
        return array_filter($values) === [];
    }
}

?>

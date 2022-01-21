<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

final class UseOnReturnAfterIfElseAssign
{
    public static function classToArray($data): array
    {
        if (method_exists($data, 'toRawArray')) {
            $properties = $data->toRawArray();
        } else {
            $properties = (array) $data;
        }

        return $properties;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

final class UseOnReturnAfterIfElseAssign
{
    public static function classToArray($data): array
    {
        $properties = method_exists($data, 'toRawArray') ? $data->toRawArray() : (array) $data;

        return $properties;
    }
}

?>

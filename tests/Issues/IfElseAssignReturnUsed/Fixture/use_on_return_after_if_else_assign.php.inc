<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IfElseAssignReturnUsed\Fixture;

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

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IfElseAssignReturnUsed\Fixture;

final class UseOnReturnAfterIfElseAssign
{
    public static function classToArray($data): array
    {
        if (method_exists($data, 'toRawArray')) {
            return $data->toRawArray();
        }

        return (array) $data;
    }
}

?>

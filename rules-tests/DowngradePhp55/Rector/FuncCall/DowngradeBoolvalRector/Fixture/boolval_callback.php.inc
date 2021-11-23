<?php

namespace Rector\Tests\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector\Fixture;

function mapToBool(array $values)
{
    return array_map('boolval', $values);
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector\Fixture;

function mapToBool(array $values)
{
    return array_map(function ($value) {
        return (bool) $value;
    }, $values);
}

?>

<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

final class ArrayAssoc
{
    public function run($data, array $items)
    {
        $data = \json_decode($data, $items[0]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

final class ArrayAssoc
{
    public function run($data, array $items)
    {
        $data = \json_decode($data, $items[0] === null ?: $items[0]);
    }
}

?>

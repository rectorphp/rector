<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

final class JustCalledArrayKeys
{
    public function hasKey(): bool
    {
        return !in_array('key', array_keys([
           'key' => ',',
           1 => ';',
        ]));
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

final class JustCalledArrayKeys
{
    public function hasKey(): bool
    {
        return !array_key_exists('key', [
           'key' => ',',
           1 => ';',
        ]);
    }
}

?>

<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector\Wrong;

final class MyClass
{
    public function hasKey(): bool
    {
        return !in_array('key', array_keys([
          'key' => ',',
          1 => ';',
        ]));
    }

    public function hasMoreThanOneArgument(): bool
    {
        return in_array('key', array_keys([
          'key' => ',',
          1 => ';',
      ], 'key'));
    }

    public function resultIntoAVariable(): void
    {
        $array = ['foo', 'bar'];
        $key = 'key';

        $result = in_array($key, array_keys($array), true);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\InArrayAndArrayKeysToArrayKeyExistsRector\Wrong;

final class MyClass
{
    public function hasKey(): bool
    {
        return !array_key_exists('key', [
          'key' => ',',
          1 => ';',
        ]);
    }

    public function hasMoreThanOneArgument(): bool
    {
        return in_array('key', array_keys([
          'key' => ',',
          1 => ';',
      ], 'key'));
    }

    public function resultIntoAVariable(): void
    {
        $array = ['foo', 'bar'];
        $key = 'key';

        $result = array_key_exists($key, $array);
    }
}

?>

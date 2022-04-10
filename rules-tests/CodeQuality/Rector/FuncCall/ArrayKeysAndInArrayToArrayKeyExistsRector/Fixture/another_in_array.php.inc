<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

final class AnotherInArray
{
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

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

final class AnotherInArray
{
    public function resultIntoAVariable(): void
    {
        $array = ['foo', 'bar'];
        $key = 'key';

        $result = array_key_exists($key, $array);
    }
}

?>

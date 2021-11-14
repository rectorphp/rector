<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class TypeChangedOnIf
{
    public function run($a, $b)
    {
        $data = [];
        if (! $a) {
            $data = new \stdClass;
        }

        $data[] = count($data) !== 0 ? 'a' : 'b';
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class TypeChangedOnIf
{
    public function run($a, $b)
    {
        $data = [];
        if (! $a) {
            $data = new \stdClass;
        }

        $data[] = (is_array($data) || $data instanceof \Countable ? count($data) : 0) !== 0 ? 'a' : 'b';
    }
}

?>

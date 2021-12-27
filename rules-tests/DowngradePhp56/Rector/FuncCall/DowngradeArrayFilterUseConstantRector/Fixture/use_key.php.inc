<?php

namespace Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\Fixture;

final class UseKey
{
    public function run()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

        var_dump(array_filter($arr, function($k) {
            return $k === 'a';
        }, ARRAY_FILTER_USE_KEY));
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\Fixture;

final class UseKey
{
    public function run()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $result = [];
        foreach (array_keys($arr) as $k) {
            if ($k === 'a') {
                $result[$k] = $arr[$k];
            }
        }

        var_dump($result);
    }
}

?>

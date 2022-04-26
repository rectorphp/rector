<?php

namespace Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\Fixture;

final class UseBoth
{
    public function run()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

        var_dump(array_filter($arr, function($v, $k) {
            return $k == 'b' || $v == 4;
        }, ARRAY_FILTER_USE_BOTH));
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\Fixture;

final class UseBoth
{
    public function run()
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $result = [];
        foreach ($arr as $k => $v) {
            if ($k == 'b' || $v == 4) {
                $result[$k] = $v;
            }
        }

        var_dump($result);
    }
}

?>

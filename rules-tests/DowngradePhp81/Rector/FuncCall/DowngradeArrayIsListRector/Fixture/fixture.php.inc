<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\Fixture;

array_is_list([1 => 'apple', 'orange']);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\Fixture;

$arrayIsList = function (array $array) : bool {
    if (function_exists('array_is_list')) {
        return array_is_list($array);
    }
    if ($array === []) {
        return true;
    }
    $current_key = 0;
    foreach ($array as $key => $noop) {
        if ($key !== $current_key) {
            return false;
        }
        ++$current_key;
    }
    return true;
};
$arrayIsList([1 => 'apple', 'orange']);

?>

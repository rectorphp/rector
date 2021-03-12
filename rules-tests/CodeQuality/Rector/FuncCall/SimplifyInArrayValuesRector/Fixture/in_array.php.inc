<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyInArrayValuesRector\Fixture;

final class InArray
{
    public function run()
    {
        $array = ['key', 'value'];
        return in_array('key', array_values($array), true);
    }

}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyInArrayValuesRector\Fixture;

final class InArray
{
    public function run()
    {
        $array = ['key', 'value'];
        return in_array('key', $array, true);
    }

}

?>

<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class ArrayListParams
{
    public function run($one, $two)
    {
        $names = ['one', 'two'];

        return compact($names);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class ArrayListParams
{
    public function run($one, $two)
    {
        $names = ['one' => $one, 'two' => $two];

        return $names;
    }
}

?>

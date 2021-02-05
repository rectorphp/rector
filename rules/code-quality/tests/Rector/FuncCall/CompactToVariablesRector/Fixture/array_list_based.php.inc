<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class ArrayListBased
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

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class ArrayListBased
{
    public function run($one, $two)
    {
        $names = ['one' => $one, 'two' => $two];

        return $names;
    }
}

?>

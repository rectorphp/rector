<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class ArrayListBased
{
    public function run()
    {
        $one = 1000;
        $two = 'hey';

        $names = ['one', 'two'];

        return compact($names);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class ArrayListBased
{
    public function run()
    {
        $one = 1000;
        $two = 'hey';

        $names = ['one' => $one, 'two' => $two];

        return $names;
    }
}

?>

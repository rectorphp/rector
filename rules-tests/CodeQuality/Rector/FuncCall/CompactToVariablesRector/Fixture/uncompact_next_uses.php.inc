<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class UncompactNextUses
{
    public function run()
    {
        $one = 1000;
        $two = 'hey';

        $names = ['one', 'two'];

        $misc = compact($names);

        return compact($names) + $misc;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class UncompactNextUses
{
    public function run()
    {
        $one = 1000;
        $two = 'hey';

        $names = ['one' => $one, 'two' => $two];

        $misc = $names;

        return $names + $misc;
    }
}

?>

<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class ArrayListBased implements RunnableInterface
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

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class ArrayListBased implements RunnableInterface
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

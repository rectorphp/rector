<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class HandleMultipleItemsToArray
{
    public function go()
    {
        $this->run('John', 1, 2, 3, 4, 5);
    }

    public function run(string $name, ...$items)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class HandleMultipleItemsToArray
{
    public function go()
    {
        $this->run('John', [1, 2, 3, 4, 5]);
    }

    public function run(string $name, array $items)
    {
    }
}

?>

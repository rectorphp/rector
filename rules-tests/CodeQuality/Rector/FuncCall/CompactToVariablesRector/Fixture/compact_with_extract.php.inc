<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class CompactWithExtract implements RunnableInterface
{
    public function run()
    {
        $values = ['result'];

        $result = 1000;

        return compact($values);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class CompactWithExtract implements RunnableInterface
{
    public function run()
    {
        $result = 1000;
        $values = ['result' => $result];

        return $values;
    }
}

?>

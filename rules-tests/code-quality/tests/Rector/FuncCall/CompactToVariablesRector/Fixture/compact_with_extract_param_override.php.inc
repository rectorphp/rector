<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class CompactWithExtractParamOverride implements RunnableInterface
{
    public function run($result = 100)
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

final class CompactWithExtractParamOverride implements RunnableInterface
{
    public function run($result = 100)
    {
        $result = 1000;
        $values = ['result' => $result];

        return $values;
    }
}

?>

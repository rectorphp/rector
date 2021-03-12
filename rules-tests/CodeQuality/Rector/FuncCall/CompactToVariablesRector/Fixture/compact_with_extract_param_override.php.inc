<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class CompactWithExtractParamOverride
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

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class CompactWithExtractParamOverride
{
    public function run($result = 100)
    {
        $result = 1000;
        $values = ['result' => $result];

        return $values;
    }
}

?>

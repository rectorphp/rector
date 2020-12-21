<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class InStirngVariable
{
    public function run()
    {
        $this->setOnClick("[Zip ID: {$modelid}]  {$e->getMessage($modelId,)}",);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class InStirngVariable
{
    public function run()
    {
        $this->setOnClick("[Zip ID: {$modelid}]  {$e->getMessage($modelId)}");
    }
}

?>

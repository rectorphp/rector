<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class InStringVariable
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

class InStringVariable
{
    public function run()
    {
        $this->setOnClick("[Zip ID: {$modelid}]  {$e->getMessage($modelId)}");
    }
}

?>

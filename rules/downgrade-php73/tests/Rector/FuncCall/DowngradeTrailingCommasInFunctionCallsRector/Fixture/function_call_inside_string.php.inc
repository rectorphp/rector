<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class FunctionCallInsideString
{
    public function run()
    {
        $value = "{$this->method("test")}";
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class FunctionCallInsideString
{
    public function run()
    {
        $value = "{$this->method("test")}";
    }
}

?>

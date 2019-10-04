<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\Fixture;

class SkipVersionCompareClass
{
    public function run()
    {
        version_compare($this->version, '5.3.0', '<');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\Fixture;

class SkipVersionCompareClass
{
    public function run()
    {
        version_compare($this->version, '5.3.0', '<');
    }
}

?>

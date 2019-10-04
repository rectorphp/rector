<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\Fixture;

class PiClass
{
    public function run()
    {
        $value = pi();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\Fixture;

class PiClass
{
    public function run()
    {
        $value = M_PI;
    }
}

?>

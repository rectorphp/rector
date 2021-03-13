<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

class FunctionWithOneRequiredAndOneOptionalParameter
{
    public function run()
    {
        $this->theFunction(1);
        $this->theFunction(1, 2);
        $this->theFunction(1, 2, 3);
    }

    private function theFunction($a, $b = 5)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

class FunctionWithOneRequiredAndOneOptionalParameter
{
    public function run()
    {
        $this->theFunction(1);
        $this->theFunction(1, 2);
        $this->theFunction(1, 2);
    }

    private function theFunction($a, $b = 5)
    {
    }
}

?>

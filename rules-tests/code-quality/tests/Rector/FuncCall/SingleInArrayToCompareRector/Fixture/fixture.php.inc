<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SingleInArrayToCompareRector\Fixture;

class Fixture
{
    public function run()
    {
        $isIt = in_array(strtolower($type), ['$this'], true);
        $isIt = in_array(strtolower($type), ['$this']);

        $isIt = in_array(strtolower($type), ['$this', 'two']);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SingleInArrayToCompareRector\Fixture;

class Fixture
{
    public function run()
    {
        $isIt = strtolower($type) === '$this';
        $isIt = strtolower($type) == '$this';

        $isIt = in_array(strtolower($type), ['$this', 'two']);
    }
}

?>

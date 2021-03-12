<?php

namespace Rector\Php53\Tests\Rector\FuncCall\DirNameFileConstantToDirConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        return dirname(__FILE__);
    }
}

?>
-----
<?php

namespace Rector\Php53\Tests\Rector\FuncCall\DirNameFileConstantToDirConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        return __DIR__;
    }
}

?>

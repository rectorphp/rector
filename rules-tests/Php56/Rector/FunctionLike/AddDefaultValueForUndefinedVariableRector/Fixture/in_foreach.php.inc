<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class InForeach
{
    public function run()
    {
        $b = [];
        foreach ($b as $c) {
        }

        echo $c;
        echo $b;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class InForeach
{
    public function run()
    {
        $c = null;
        $b = [];
        foreach ($b as $c) {
        }

        echo $c;
        echo $b;
    }
}

?>

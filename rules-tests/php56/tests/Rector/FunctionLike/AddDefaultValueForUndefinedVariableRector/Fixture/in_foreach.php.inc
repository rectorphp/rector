<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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

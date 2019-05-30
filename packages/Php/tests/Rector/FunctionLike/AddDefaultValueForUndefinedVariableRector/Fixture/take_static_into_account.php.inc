<?php

namespace Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class WithAStaticVariableInsideMethod
{
    public function run()
    {
        static $variable = null;
        if (null === $variable) {
            $variable = 'not null anymore';
        }

        return $variable;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class WithAStaticVariableInsideMethod
{
    public function run()
    {
        static $variable = null;
        if (null === $variable) {
            $variable = 'not null anymore';
        }

        return $variable;
    }
}

?>

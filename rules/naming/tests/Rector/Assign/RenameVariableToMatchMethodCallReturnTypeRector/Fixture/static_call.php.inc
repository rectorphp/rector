<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner;

class StaticCallClass
{
    public function run()
    {
        $a = static::getFastRunner();
        $a->exit();
    }

    /**
     * @return \Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner
     */
    public static function getFastRunner()
    {
        return new FastRunner();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner;

class StaticCallClass
{
    public function run()
    {
        $fastRunner = static::getFastRunner();
        $fastRunner->exit();
    }

    /**
     * @return \Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\FastRunner
     */
    public static function getFastRunner()
    {
        return new FastRunner();
    }
}

?>

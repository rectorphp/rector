<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class StaticCallClass
{
    public function run()
    {
        $a = static::getFastRunner();
        $a->exit();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class StaticCallClass
{
    public function run()
    {
        $fastRunner = static::getFastRunner();
        $fastRunner->exit();
    }
}

?>

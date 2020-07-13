<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class SomeClass
{
    public function run()
    {
        $a = $this->getFastRunner();
        $a->exit();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class SomeClass
{
    public function run()
    {
        $fastRunner = $this->getFastRunner();
        $fastRunner->exit();
    }
}

?>

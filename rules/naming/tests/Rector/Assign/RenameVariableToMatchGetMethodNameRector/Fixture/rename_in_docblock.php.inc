<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class RanameInDocblockClass
{
    public function run()
    {
        /** @var FastRunner $a */
        $a = $this->getFastRunner();
        $a->exit();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class RanameInDocblockClass
{
    public function run()
    {
        /** @var FastRunner $fastRunner */
        $fastRunner = $this->getFastRunner();
        $fastRunner->exit();
    }
}

?>

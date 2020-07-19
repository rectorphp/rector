<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Source\FastRunner;

class DocBlockToo
{
    public function run()
    {
        /** @var FastRunner $a */
        $a = $this->getFastRunner();
        $a->exit();
    }

    public function getFastRunner(): FastRunner
    {
        return new FastRunner();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

use Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Source\FastRunner;

class DocBlockToo
{
    public function run()
    {
        /** @var FastRunner $fastRunner */
        $fastRunner = $this->getFastRunner();
        $fastRunner->exit();
    }

    public function getFastRunner(): FastRunner
    {
        return new FastRunner();
    }
}

?>

<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class LastInChainClass
{
    public function run()
    {
        $a = $this->getRunners()->getFast();
        $a->exit();
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\Fixture;

class LastInChainClass
{
    public function run()
    {
        $fast = $this->getRunners()->getFast();
        $fast->exit();
    }
}

?>

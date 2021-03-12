<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\Runners;

class LastInChainClass
{
    public function run()
    {
        $a = $this->getRunners()->getFast();
        $a->exit();
    }

    public function getRunners(): Runners
    {
        return new Runners();
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Source\Runners;

class LastInChainClass
{
    public function run()
    {
        $fastRunner = $this->getRunners()->getFast();
        $fastRunner->exit();
    }

    public function getRunners(): Runners
    {
        return new Runners();
    }
}

?>

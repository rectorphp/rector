<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class KeepProperty
{
    /**
     * @var EliteManager
     */
    private $nonSense;

    public function __construct(EliteManager $nonSense)
    {
        $this->nonSense = $nonSense;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class KeepProperty
{
    /**
     * @var EliteManager
     */
    private $nonSense;

    public function __construct(EliteManager $eliteManager)
    {
        $this->nonSense = $eliteManager;
    }
}

?>

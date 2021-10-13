<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class DynamicProperty
{
    public function __construct(EliteManager $nonSense)
    {
        try {
            $doesNotMatter = $this->$nonSense;
        } catch (\Throwable $e) {}
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class DynamicProperty
{
    public function __construct(EliteManager $eliteManager)
    {
        try {
            $doesNotMatter = $this->$eliteManager;
        } catch (\Throwable $e) {}
    }
}

?>

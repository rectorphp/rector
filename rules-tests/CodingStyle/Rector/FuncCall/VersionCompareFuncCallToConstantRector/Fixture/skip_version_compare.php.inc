<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class SkipVersionCompare
{
    public function run()
    {
        version_compare($this->version, '5.3.0', '<');
    }
}

?>

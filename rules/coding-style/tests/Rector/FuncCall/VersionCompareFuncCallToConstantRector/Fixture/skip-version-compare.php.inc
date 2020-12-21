<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class SkipVersionCompare
{
    public function run()
    {
        version_compare($this->version, '5.3.0', '<');
    }
}

?>

<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class Skip-version-compare
{
    public function run()
    {
        version_compare($this->version, '5.3.0', '<');
    }
}

?>

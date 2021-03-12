<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use Rector\Tests\DynamicTypeAnalysis\ProbeStorage\StaticInMemoryProbeStorage;

final class WithStaticCall
{
    public function run()
    {
        $probeStorage = new StaticInMemoryProbeStorage();
        TypeStaticProbe::setProbeStorage($probeStorage);

        $probeStorage::clear();
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use Rector\Tests\DynamicTypeAnalysis\ProbeStorage\StaticInMemoryProbeStorage;

final class WithStaticCall
{
    public function run()
    {
        $staticInMemoryProbeStorage = new StaticInMemoryProbeStorage();
        TypeStaticProbe::setProbeStorage($staticInMemoryProbeStorage);

        $staticInMemoryProbeStorage::clear();
    }
}

?>

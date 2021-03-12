<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use Rector\DynamicTypeAnalysis\Tests\ProbeStorage\StaticInMemoryProbeStorage;

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

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;
use Rector\DynamicTypeAnalysis\Tests\ProbeStorage\StaticInMemoryProbeStorage;

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

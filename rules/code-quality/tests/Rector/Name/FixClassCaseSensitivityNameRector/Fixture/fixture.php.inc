<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\Misscasetypedclass;

final class Fixture
{
    public function run()
    {
        $misscasetypedclass = new Misscasetypedclass();
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class Fixture
{
    public function run()
    {
        $misscasetypedclass = new \Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass();
    }
}

?>

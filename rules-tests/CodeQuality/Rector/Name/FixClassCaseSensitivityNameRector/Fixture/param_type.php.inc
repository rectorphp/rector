<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class ParamType
{
    public function run(misscasetypedclass $misscasetypedclass)
    {
        return $misscasetypedclass;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class ParamType
{
    public function run(\Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass $misscasetypedclass)
    {
        return $misscasetypedclass;
    }
}

?>

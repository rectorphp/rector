<?php

namespace Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class UnionParamType
{
    public function run(string|misscasetypedclass $misscasetypedclass)
    {
        return $misscasetypedclass;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class UnionParamType
{
    public function run(string|\Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass $misscasetypedclass)
    {
        return $misscasetypedclass;
    }
}

?>

<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class NamespaceAndConstants
{
    public function run()
    {
        $fatherSon = misscasetypedclass::SOME_CONST;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass;

final class NamespaceAndConstants
{
    public function run()
    {
        $fatherSon = \Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Source\MissCaseTypedClass::SOME_CONST;
    }
}

?>

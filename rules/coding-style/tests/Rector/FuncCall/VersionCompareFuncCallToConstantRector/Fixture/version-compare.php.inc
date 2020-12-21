<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class VersionCompare
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3.0', '<');
        version_compare(PHP_VERSION, '5.3.0', '>');
        version_compare(PHP_VERSION, '5.3.0', '=');

        version_compare('5.3.0', PHP_VERSION, 'lt');
        version_compare('5.3.0', PHP_VERSION, 'gt');
        version_compare('5.3.0', PHP_VERSION, 'eq');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class VersionCompare
{
    public function run()
    {
        PHP_VERSION_ID < 50300;
        PHP_VERSION_ID > 50300;
        PHP_VERSION_ID === 50300;

        50300 < PHP_VERSION_ID;
        50300 > PHP_VERSION_ID;
        50300 === PHP_VERSION_ID;
    }
}

?>

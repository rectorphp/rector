<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class ShortVersionCompare
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3', '<');
        version_compare(PHP_VERSION, '5.3', '>');
        version_compare(PHP_VERSION, '5.3', '=');

        version_compare('5.3', PHP_VERSION, 'lt');
        version_compare('5.3', PHP_VERSION, 'gt');
        version_compare('5.3', PHP_VERSION, 'eq');
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class ShortVersionCompare
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

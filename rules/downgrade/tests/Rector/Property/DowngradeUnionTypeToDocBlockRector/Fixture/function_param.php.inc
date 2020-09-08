<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\Fixture;

class ClassMethod
{
    public function run(): int|string
    {
        if (mt_rand()) {
            return 1;
        }

        return 'value';
    }
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\Fixture;

class ClassMethod
{
    /**
     * @return int|string
     */
    public function run()
    {
        if (mt_rand()) {
            return 1;
        }

        return 'value';
    }
}

?>

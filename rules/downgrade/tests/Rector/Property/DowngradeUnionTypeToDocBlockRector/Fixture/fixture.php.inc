<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\Fixture;

class SomeClass
{
    public int|string $value;
}

?>
-----
<?php

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector\Fixture;

class SomeClass
{
    /**
     * @var int|string
     */
    public $value;
}

?>

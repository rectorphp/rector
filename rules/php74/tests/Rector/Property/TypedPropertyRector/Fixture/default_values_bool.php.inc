<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesBool
{
    private $size = false;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValuesBool
{
    private bool $size = false;
}

?>

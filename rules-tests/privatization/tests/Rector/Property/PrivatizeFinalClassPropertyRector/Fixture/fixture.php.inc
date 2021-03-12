<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

final class Fixture
{
    protected $value;
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture;

final class Fixture
{
    private $value;
}

?>

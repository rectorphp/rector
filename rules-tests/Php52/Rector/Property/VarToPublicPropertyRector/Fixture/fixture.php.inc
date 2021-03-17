<?php

namespace Rector\Tests\Php52\Rector\Property\VarToPublicPropertyRector\Fixture;

final class Fixture
{
    var $name = 'Tom';
    public $age = 50;
}

?>
-----
<?php

namespace Rector\Tests\Php52\Rector\Property\VarToPublicPropertyRector\Fixture;

final class Fixture
{
    public $name = 'Tom';
    public $age = 50;
}

?>

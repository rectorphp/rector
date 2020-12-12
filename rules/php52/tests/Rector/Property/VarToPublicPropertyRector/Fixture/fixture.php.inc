<?php

namespace Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector\Fixture;

final class Fixture
{
    var $name = 'Tom';
    public $age = 50;
}

?>
-----
<?php

namespace Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector\Fixture;

final class Fixture
{
    public $name = 'Tom';
    public $age = 50;
}

?>

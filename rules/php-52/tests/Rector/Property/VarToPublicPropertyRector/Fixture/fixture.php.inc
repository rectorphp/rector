<?php

namespace Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector\Fixture;

final class SomeController
{
    var $name = 'Tom';
    public $age = 50;
}

?>
-----
<?php

namespace Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector\Fixture;

final class SomeController
{
    public $name = 'Tom';
    public $age = 50;
}

?>

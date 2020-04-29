<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveNullPropertyInitializationRector\Fixture;

class MultipleProperties
{
    private $lowerCase = null, $upperCase = null;
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveNullPropertyInitializationRector\Fixture;

class MultipleProperties
{
    private $lowerCase, $upperCase;
}

?>

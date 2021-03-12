<?php

namespace Rector\Tests\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\Fixture;

class MultipleProperties
{
    private $lowerCase = null, $upperCase = null;
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\Fixture;

class MultipleProperties
{
    private $lowerCase, $upperCase;
}

?>

<?php

namespace Rector\DeadCode\Tests\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\Fixture;

class MultipleProperties
{
    private $lowerCase = null, $upperCase = null;
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\Fixture;

class MultipleProperties
{
    private $lowerCase, $upperCase;
}

?>

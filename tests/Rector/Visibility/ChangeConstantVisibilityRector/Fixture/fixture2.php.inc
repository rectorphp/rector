<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source;

class AnotherClassWithInvalidConstants
{
    public const TO_BE_PRIVATE_CONSTANT = 1;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source;

class AnotherClassWithInvalidConstants
{
    private const TO_BE_PRIVATE_CONSTANT = 1;
}

?>

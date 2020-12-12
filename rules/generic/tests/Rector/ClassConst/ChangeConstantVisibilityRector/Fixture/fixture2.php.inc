<?php

namespace Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture;

class AnotherClassWithInvalidConstants
{
    public const TO_BE_PRIVATE_CONSTANT = 1;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture;

class AnotherClassWithInvalidConstants
{
    private const TO_BE_PRIVATE_CONSTANT = 1;
}

?>

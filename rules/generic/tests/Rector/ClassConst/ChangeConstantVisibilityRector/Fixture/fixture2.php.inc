<?php

namespace Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture;

class Fixture2
{
    public const TO_BE_PRIVATE_CONSTANT = 1;
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture;

class Fixture2
{
    private const TO_BE_PRIVATE_CONSTANT = 1;
}

?>

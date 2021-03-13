<?php

namespace Rector\Tests\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector\Fixture;

class FromZero
{
    const MODE_ON = 100;

    const MODE_OFF = 0;

    const MODE_MAYBE = 125;
}

?>
-----
<?php

namespace Rector\Tests\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector\Fixture;

class FromZero
{
    const MODE_OFF = 0;
    const MODE_ON = 100;

    const MODE_MAYBE = 125;
}

?>

<?php

namespace Rector\Order\Tests\Rector\Class_\OrderClassConstantsByIntegerValueRector\Fixture;

class FromZero
{
    const MODE_ON = 100;

    const MODE_OFF = 0;

    const MODE_MAYBE = 125;
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderClassConstantsByIntegerValueRector\Fixture;

class FromZero
{
    const MODE_OFF = 0;
    const MODE_ON = 100;

    const MODE_MAYBE = 125;
}

?>

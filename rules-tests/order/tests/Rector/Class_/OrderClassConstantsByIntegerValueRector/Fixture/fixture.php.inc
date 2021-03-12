<?php

namespace Rector\Order\Tests\Rector\Class_\OrderClassConstantsByIntegerValueRector\Fixture;

class Fixture
{
    const MODE_ON = 100;

    const MODE_OFF = 250;

    const MODE_MAYBE = 125;
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderClassConstantsByIntegerValueRector\Fixture;

class Fixture
{
    const MODE_ON = 100;

    const MODE_MAYBE = 125;
    const MODE_OFF = 250;
}

?>

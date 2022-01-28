<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;
use DateTimeZone;

class PassNonDynamicArg
{
    private DateTime $dateTime;

    public function __construct(
        ?DateTime $dateTime = null
    ) {
        $this->dateTime = $dateTime ?? new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;
use DateTimeZone;

class PassNonDynamicArg
{
    public function __construct(private DateTime $dateTime = new DateTime('now', new DateTimeZone('Asia/Jakarta')))
    {
    }
}

?>

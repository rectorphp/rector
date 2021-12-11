<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime;

class InnerReturnDateTime
{
    public static function run(DateTime $dateTime): DateTime
    {
        function () {
            return new DateTime('now');
        };

        return $dateTime;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use DateTime;

class InnerReturnDateTime
{
    /**
     * @param \DateTime|\DateTimeImmutable $dateTime
     * @return \DateTime|\DateTimeImmutable
     */
    public static function run(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        function () {
            return new DateTime('now');
        };

        return $dateTime;
    }
}

?>

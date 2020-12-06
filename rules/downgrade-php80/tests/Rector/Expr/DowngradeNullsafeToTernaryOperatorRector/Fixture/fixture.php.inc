<?php

namespace Rector\DowngradePhp80\Tests\Rector\Expr\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class SomeClass
{
    public function run($booking)
    {
        $dateAsString = $booking->getStartDate()?->asDateTimeString();
        $dateAsString = $booking->startDate?->dateTimeString;
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Expr\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class SomeClass
{
    public function run($booking)
    {
        $dateAsString = $booking->getStartDate() ? $booking->getStartDate()->asDateTimeString() : null;
        $dateAsString = $booking->startDate ? $booking->startDate->dateTimeString : null;
    }
}

?>

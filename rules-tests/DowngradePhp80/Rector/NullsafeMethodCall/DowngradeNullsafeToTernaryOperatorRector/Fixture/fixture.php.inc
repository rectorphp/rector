<?php

namespace Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class Fixture
{
    public function run($booking)
    {
        // with argument
        $dateAsString = $booking->getStartDate($args1)?->asDateTimeString($arg2);

        // without argument, one is method call, next property fetch
        $dateAsString = $booking->getStartDate()?->asDateTimeString;

        // without argument, one is property call, next method fetch
        $dateAsString = $booking->getStartDate?->asDateTimeString();

        // all are property fetch
        $dateAsString = $booking->getStartDate?->asDateTimeString;

        $getStartDate = true;
        $bookingGetStartDate = true;

        // previously named vars aren't reused
        $dateAsString = $booking->getStartDate?->asDateTimeString;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class Fixture
{
    public function run($booking)
    {
        // with argument
        $dateAsString = ($getStartDate = $booking->getStartDate($args1)) ? $getStartDate->asDateTimeString($arg2) : null;

        // without argument, one is method call, next property fetch
        $dateAsString = ($getStartDate = $booking->getStartDate()) ? $getStartDate->asDateTimeString : null;

        // without argument, one is property call, next method fetch
        $dateAsString = ($bookingGetStartDate = $booking->getStartDate) ? $bookingGetStartDate->asDateTimeString() : null;

        // all are property fetch
        $dateAsString = ($bookingGetStartDate = $booking->getStartDate) ? $bookingGetStartDate->asDateTimeString : null;

        $getStartDate = true;
        $bookingGetStartDate = true;

        // previously named vars aren't reused
        $dateAsString = ($bookingGetStartDate2 = $booking->getStartDate) ? $bookingGetStartDate2->asDateTimeString : null;
    }
}

?>

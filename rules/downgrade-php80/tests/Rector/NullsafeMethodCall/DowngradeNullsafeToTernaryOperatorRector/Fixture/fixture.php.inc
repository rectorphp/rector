<?php

namespace Rector\DowngradePhp80\Tests\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

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
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\Fixture;

class Fixture
{
    public function run($booking)
    {
        // with argument
        $dateAsString = $booking->getStartDate($args1) ? $booking->getStartDate($args1)->asDateTimeString($arg2) : null;

        // without argument, one is method call, next property fetch
        $dateAsString = $booking->getStartDate() ? $booking->getStartDate()->asDateTimeString : null;

        // without argument, one is property call, next method fetch
        $dateAsString = $booking->getStartDate ? $booking->getStartDate->asDateTimeString() : null;

        // all are property fetch
        $dateAsString = $booking->getStartDate ? $booking->getStartDate->asDateTimeString : null;
    }
}

?>

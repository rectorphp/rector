<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use \DateTime;

class FixtureUse {
    public function methodWithDateTime(DateTime $dateTime)
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

use \DateTime;

class FixtureUse {
    /**
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime(\DateTimeInterface $dateTime)
    {
        return true;
    }
}

?>

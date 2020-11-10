<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureDocblock {
    /**
     * @param mixed $mixed
     */
    public function methodWithDateTime($mixed, \DateTime $dateTime)
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureDocblock {
    /**
     * @param mixed $mixed
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime($mixed, \DateTimeInterface $dateTime)
    {
        return true;
    }
}

?>

<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureNullable {
    public function methodWithDateTime(?\DateTime $dateTime)
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureNullable {
    /**
     * @param \DateTime|\DateTimeImmutable|null $dateTime
     */
    public function methodWithDateTime(?\DateTimeInterface $dateTime)
    {
        return true;
    }
}

?>

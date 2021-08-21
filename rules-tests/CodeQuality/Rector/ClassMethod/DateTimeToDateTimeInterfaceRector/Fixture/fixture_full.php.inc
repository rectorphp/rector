<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

final class FixtureFull
{
    private \DateTime $date;

    private ?\DateTime $nullableDate;

    public function __construct(\DateTime $date, ?\DateTime $nullableDate)
    {
        $this->date = $date;
        $this->nullableDate = $nullableDate;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getNullableDate(): ?\DateTime
    {
        return $this->nullableDate;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

final class FixtureFull
{
    /**
     * @var \DateTime|\DateTimeImmutable
     */
    private \DateTimeInterface $date;

    /**
     * @var \DateTime|\DateTimeImmutable|null
     */
    private ?\DateTimeInterface $nullableDate;

    /**
     * @param \DateTime|\DateTimeImmutable $date
     * @param \DateTime|\DateTimeImmutable|null $nullableDate
     */
    public function __construct(\DateTimeInterface $date, ?\DateTimeInterface $nullableDate)
    {
        $this->date = $date;
        $this->nullableDate = $nullableDate;
    }

    /**
     * @return \DateTime|\DateTimeImmutable
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return \DateTime|\DateTimeImmutable|null
     */
    public function getNullableDate(): ?\DateTimeInterface
    {
        return $this->nullableDate;
    }
}

?>

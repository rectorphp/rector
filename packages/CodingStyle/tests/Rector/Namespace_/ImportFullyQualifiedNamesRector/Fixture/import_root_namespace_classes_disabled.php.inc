<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class ImportRootNamespaceClassesDisabled
{
    /**
     * @var \DateTime
     */
    private $date;

    public function __construct()
    {
        /** @var \DateTime $currentDate */
        $currentDate = new \DateTime();

        $this->date = $currentDate;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }
}

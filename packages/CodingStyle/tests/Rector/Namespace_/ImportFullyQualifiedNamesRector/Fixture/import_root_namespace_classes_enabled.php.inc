<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class ImportRootNamespaceClassesEnabled
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
?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use DateTime;
final class ImportRootNamespaceClassesEnabled
{
    /**
     * @var DateTime
     */
    private $date;

    public function __construct()
    {
        /** @var DateTime $currentDate */
        $currentDate = new DateTime();

        $this->date = $currentDate;
    }

    public function setDate(?DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }
}
?>

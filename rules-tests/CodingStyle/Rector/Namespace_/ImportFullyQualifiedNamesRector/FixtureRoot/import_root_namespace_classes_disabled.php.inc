<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class ImportRootNamespaceClassesDisabled
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Response
     */
    private $response;

    public function __construct()
    {
        /** @var \DateTime $currentDate */
        $currentDate = new \DateTime();

        /** @var \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Response $response */
        $response = new \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Response();

        $this->date = $currentDate;
        $this->response = $response;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setResponse(?\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Response
    {
        return $this->response;
    }
}
?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Response;
final class ImportRootNamespaceClassesDisabled
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var Response
     */
    private $response;

    public function __construct()
    {
        /** @var \DateTime $currentDate */
        $currentDate = new \DateTime();

        /** @var Response $response */
        $response = new Response();

        $this->date = $currentDate;
        $this->response = $response;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
?>

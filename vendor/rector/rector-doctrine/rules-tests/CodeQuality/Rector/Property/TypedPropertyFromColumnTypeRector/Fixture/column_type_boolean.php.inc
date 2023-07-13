<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ColumnTypeBoolean
{
    #[ORM\Column(type: 'boolean', length: 255, nullable: true)]
    private $property;

    public function getProperty(): ?bool
    {
        return $this->property;
    }

    public function setProperty(?bool $property): void
    {
        $this->property = $property;
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ColumnTypeBoolean
{
    #[ORM\Column(type: 'boolean', length: 255, nullable: true)]
    private ?bool $property = null;

    public function getProperty(): ?bool
    {
        return $this->property;
    }

    public function setProperty(?bool $property): void
    {
        $this->property = $property;
    }
}

?>

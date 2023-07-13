<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;

use Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctrineOneToOne
{
    #[ORM\OneToOne(targetEntity: Company::class)]
    private $company;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;

use Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctrineOneToOne
{
    #[ORM\OneToOne(targetEntity: Company::class)]
    private ?\Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company $company = null;
}

?>

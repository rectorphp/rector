<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctrineManyToOne
{
    #[ORM\ManyToOne(targetEntity:"Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company", inversedBy:"userBuildings")]
    #[ORM\JoinColumn(name:"company_id", nullable:true, referencedColumnName:"id")]
    private $company2;

    #[ORM\ManyToOne(targetEntity:"Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company", inversedBy:"userBuildings")]
    #[ORM\JoinColumn(name:"company_id", referencedColumnName:"id", nullable:false)]
    private $company3;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctrineManyToOne
{
    #[ORM\ManyToOne(targetEntity:"Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company", inversedBy:"userBuildings")]
    #[ORM\JoinColumn(name:"company_id", nullable:true, referencedColumnName:"id")]
    private ?\Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company $company2 = null;

    #[ORM\ManyToOne(targetEntity:"Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company", inversedBy:"userBuildings")]
    #[ORM\JoinColumn(name:"company_id", referencedColumnName:"id", nullable:false)]
    private ?\Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector\Source\Company $company3 = null;
}

?>

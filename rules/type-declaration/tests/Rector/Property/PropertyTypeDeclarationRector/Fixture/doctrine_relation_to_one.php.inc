<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelationToOne
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", nullable=true, referencedColumnName="id")
     */
    private $company2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private $company3;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelationToOne
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @var \App\Company\Entity\Company|null
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", nullable=true, referencedColumnName="id")
     * @var \App\Company\Entity\Company|null
     */
    private $company2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     * @var \App\Company\Entity\Company
     */
    private $company3;
}

?>

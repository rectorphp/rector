<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationToOne
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     */
    private $related;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    private $company2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     */
    private $company3;

    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     */
    private $company4;

    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company5;

    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company6;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelated
{
}

class DoctrineRelationToOne
{
    /**
     * @ORM\OneToOne(targetEntity="DoctrineRelated")
     * @var \Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\DoctrineRelated
     */
    private $related;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @var \App\Company\Entity\Company|null
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     * @var \App\Company\Entity\Company|null
     */
    private $company2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Company\Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=false)
     * @var \App\Company\Entity\Company
     */
    private $company3;

    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @var \App\Company\Entity\Company|null
     */
    private $company4;

    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     * @var \App\Company\Entity\Company|null
     */
    private $company5;

    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     * @var \App\Company\Entity\Company
     */
    private $company6;
}

?>

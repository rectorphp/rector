<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Product;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Car;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Entity as Entity;

class DoctrineRelationImported
{
    /**
     * @ORM\OneToMany(targetEntity="Product")
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity="Car")
     */
    private $cars;

    /**
     * @ORM\ManyToOne(targetEntity="Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity="Entity\Company")
     */
    private $company2;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Product;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Car;
use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Entity as Entity;

class DoctrineRelationImported
{
    /**
     * @ORM\OneToMany(targetEntity="Product")
     * @var Product[]|\Doctrine\Common\Collections\Collection
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity="Car")
     * @var Car[]|\Doctrine\Common\Collections\Collection
     */
    private $cars;

    /**
     * @ORM\ManyToOne(targetEntity="Entity\Company", inversedBy="userBuildings")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     * @var Entity\Company|null
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity="Entity\Company")
     * @var Entity\Company
     */
    private $company2;
}

?>

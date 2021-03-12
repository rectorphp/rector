<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelationToMany
{
    /**
     * @ORM\OneToMany(targetEntity="App\Product")
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity="App\Car")
     */
    private $cars;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineRelationToMany
{
    /**
     * @ORM\OneToMany(targetEntity="App\Product")
     * @var \App\Product[]|\Doctrine\Common\Collections\Collection
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity="App\Car")
     * @var \App\Car[]|\Doctrine\Common\Collections\Collection
     */
    private $cars;
}

?>

<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToManyRelationTypeRector\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctrineRelationToMany
{
    #[ORM\OneToMany(targetEntity:"App\Product")]
    private $products;

    #[ORM\ManyToMany(targetEntity:"App\Car")]
    private $cars;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->cars = new ArrayCollection();
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromToManyRelationTypeRector\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DoctrineRelationToMany
{
    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Product>
     */
    #[ORM\OneToMany(targetEntity:"App\Product")]
    private \Doctrine\Common\Collections\Collection $products;

    /**
     * @var \Doctrine\Common\Collections\Collection<\App\Car>
     */
    #[ORM\ManyToMany(targetEntity:"App\Car")]
    private \Doctrine\Common\Collections\Collection $cars;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->cars = new ArrayCollection();
    }
}

?>

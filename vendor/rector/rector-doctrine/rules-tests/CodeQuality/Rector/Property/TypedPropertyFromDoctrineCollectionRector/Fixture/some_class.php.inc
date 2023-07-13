<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromDoctrineCollectionRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromDoctrineCollectionRector\Source\Entity\TrainingTerm;

/**
 * @ORM\Entity
 */
class DoctrineCollection
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TrainingTerm", mappedBy="training")
     * @var TrainingTerm[]|Collection
     */
    private $trainingTerms;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromDoctrineCollectionRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromDoctrineCollectionRector\Source\Entity\TrainingTerm;

/**
 * @ORM\Entity
 */
class DoctrineCollection
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TrainingTerm", mappedBy="training")
     * @var TrainingTerm[]|Collection
     */
    private \Doctrine\Common\Collections\Collection $trainingTerms;
}

?>

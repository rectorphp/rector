<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Pehapkari\Training\Entity\TrainingTerm;

/**
 * @ORM\Entity
 */
class DoctrineCollection
{
    /**
     * @ORM\OneToMany(targetEntity="Pehapkari\Training\Entity\TrainingTerm", mappedBy="training")
     * @var TrainingTerm[]|Collection
     */
    private $trainingTerms;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Pehapkari\Training\Entity\TrainingTerm;

/**
 * @ORM\Entity
 */
class DoctrineCollection
{
    /**
     * @ORM\OneToMany(targetEntity="Pehapkari\Training\Entity\TrainingTerm", mappedBy="training")
     * @var TrainingTerm[]|Collection
     */
    private \Doctrine\Common\Collections\Collection $trainingTerms;
}

?>

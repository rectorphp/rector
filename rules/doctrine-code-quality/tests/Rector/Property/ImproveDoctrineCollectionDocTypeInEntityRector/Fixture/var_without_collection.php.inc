<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class VarWithoutCollection
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Training[]
     */
    private $trainings = [];
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class VarWithoutCollection
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var \Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training[]|\Doctrine\Common\Collections\Collection<int, \Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training>
     */
    private $trainings = [];
}

?>

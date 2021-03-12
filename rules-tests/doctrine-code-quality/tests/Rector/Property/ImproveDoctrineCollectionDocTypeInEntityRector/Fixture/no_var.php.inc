<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class NoVar
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     */
    private $trainings = [];
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class NoVar
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var \Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training[]|\Doctrine\Common\Collections\Collection<int, \Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training>
     */
    private $trainings = [];
}

?>

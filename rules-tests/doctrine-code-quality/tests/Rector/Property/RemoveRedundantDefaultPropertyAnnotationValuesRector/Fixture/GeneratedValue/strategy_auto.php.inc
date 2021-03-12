<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\GeneratedValue;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class StrategyAuto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id = null;
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\GeneratedValue;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class StrategyAuto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id = null;
}

?>

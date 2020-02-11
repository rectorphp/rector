<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineEntityRector\Fixture;

class HasOne extends \AppModel
{
    public $hasOne = [
        'SuperCoolName' => [
            'className' => 'HasOneType',
        ],
    ];
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineEntityRector\Fixture;

use Doctrine\Mapping\Annotation as ORM;
/**
 * @ORM\Entity
 */
class HasOne
{
    /**
     * @ORM\OneToOne(targetEntity="HasOneType")
     */
    private $superCoolName;
}

?>

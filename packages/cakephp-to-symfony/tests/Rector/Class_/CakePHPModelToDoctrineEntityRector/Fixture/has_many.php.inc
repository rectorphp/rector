<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineEntityRector\Fixture;

class HasMany extends \AppModel
{
    public $hasMany = [
        'SuperCoolName' => [
            'className' => 'HasManyType',
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
class HasMany
{
    /**
     * @ORM\OneToMany(targetEntity="HasManyType")
     */
    private $superCoolName;
}

?>

<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineEntityRector\Fixture;

class Activity extends \AppModel
{
    public $belongsTo = [
        'ActivityType' => [
            'className' => 'ActivityType',
            'foreignKey' => 'activity_type_id',
            'dependent' => false,
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
class Activity
{
    /**
     * @ORM\ManyToOne(targetEntity="ActivityType")
     * @ORM\JoinColumn(name="activity_type_id")
     */
    private $activityType;
}

?>

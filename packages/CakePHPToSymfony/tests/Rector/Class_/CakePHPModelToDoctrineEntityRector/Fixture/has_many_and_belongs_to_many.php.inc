<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineEntityRector\Fixture;

class HasManyAndBelongsToMany extends \AppModel
{
    public $hasAndBelongsToMany = [
        'MemberOf' => [
            'className' => 'Group',
        ]
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
class HasManyAndBelongsToMany
{
    /**
     * @ORM\ManyToMany(targetEntity="Group")
     */
    private $memberOf;
}

?>

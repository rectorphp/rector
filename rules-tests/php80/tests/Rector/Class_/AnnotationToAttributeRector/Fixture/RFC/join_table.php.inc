<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class JoinTable
{
    /**
     * @ORM\JoinTable(name="users_phonenumbers",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="phonenumber_id", referencedColumnName="id", unique=true)}
     * )
     */
    public $name;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class JoinTable
{
    #[@ORM\JoinTable(name: 'users_phonenumbers')]
    #[@ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[@ORM\InverseJoinColumn(name: 'phonenumber_id', referencedColumnName: 'id', unique: true)]
    public $name;
}

?>

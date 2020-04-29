<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class ManyToMany
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
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

class ManyToMany
{
    <<ORM\ManyToMany(Phonenumber::class)>>
    <<ORM\JoinTable("users_phonenumbers")>>
    <<ORM\JoinColumn("user_id", "id")>>
    <<ORM\InverseJoinColumn("phonenumber_id", "id", ORM\JoinColumn::UNIQUE)>>
    public $name;
}

?>

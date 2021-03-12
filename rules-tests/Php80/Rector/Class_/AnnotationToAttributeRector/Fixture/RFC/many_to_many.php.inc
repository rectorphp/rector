<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class ManyToMany
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    public $name;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class ManyToMany
{
    #[@ORM\ManyToMany(targetEntity: 'Phonenumber')]
    public $name;
}

?>

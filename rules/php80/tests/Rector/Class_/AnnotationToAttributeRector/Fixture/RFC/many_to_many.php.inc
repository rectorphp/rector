<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

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

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class ManyToMany
{
    #[@ORM\ManyToMany(targetEntity: 'Phonenumber')]
    public $name;
}

?>

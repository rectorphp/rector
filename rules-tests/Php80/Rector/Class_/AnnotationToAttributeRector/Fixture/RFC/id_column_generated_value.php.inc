<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class IdColumnGeneratedvalue
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    public $name;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class IdColumnGeneratedvalue
{
    #[@ORM\Column(type: 'integer')]
    #[@ORM\GeneratedValue]
    public $name;
}

?>

<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

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

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;

class IdColumnGeneratedvalue
{
    #[@ORM\Column(type: 'integer')]
    #[@ORM\GeneratedValue]
    public $name;
}

?>

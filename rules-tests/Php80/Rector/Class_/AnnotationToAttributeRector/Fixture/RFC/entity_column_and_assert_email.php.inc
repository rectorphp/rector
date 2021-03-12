<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class EntityColumnAndAssertEmail
{
    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\Email(message="The email '{{ value }}' is not a valid email.")
     */
    public $name;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class EntityColumnAndAssertEmail
{
    #[@ORM\Column(type: 'string', unique: true)]
    #[\Symfony\Component\Validator\Constraints\Email(message: 'This value is not a valid email address.')]
    public $name;
}

?>

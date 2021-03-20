<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Symfony\Component\Validator\Constraints as Assert;

class EntityColumnAndAssertEmail
{
    /**
     * @Assert\Email(message="The email '{{ value }}' is not a valid email.")
     */
    public $name;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Symfony\Component\Validator\Constraints as Assert;

class EntityColumnAndAssertEmail
{
    #[\Symfony\Component\Validator\Constraints\Email(message: 'This value is not a valid email address.')]
    public $name;
}

?>

<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class EntityColumnAndAssertEmail
{
    /**
     * @Assert\Email(message="The email '{{ value }}' is not a valid email.")
     */
    public $name;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class EntityColumnAndAssertEmail
{
    #[\Symfony\Component\Validator\Constraints\Email(message: 'The email \'{{ value }}\' is not a valid email.')]
    public $name;
}

?>

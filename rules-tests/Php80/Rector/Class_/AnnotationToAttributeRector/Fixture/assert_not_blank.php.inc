<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class EntityColumnAndAssertNotBlankWithBoolean
{
    /**
     * @Assert\NotBlank(allowNull=true)
     */
    public $name;

    /**
     * @Assert\NotBlank(allowNull=false)
     */
    public $surname;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class EntityColumnAndAssertNotBlankWithBoolean
{
    #[\Symfony\Component\Validator\Constraints\NotBlank(allowNull: true)]
    public $name;

    #[\Symfony\Component\Validator\Constraints\NotBlank(allowNull: false)]
    public $surname;
}

?>

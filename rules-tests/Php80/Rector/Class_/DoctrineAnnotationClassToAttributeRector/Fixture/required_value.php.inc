<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @annotation
 */
final class RequiredValue
{
    /** @Required */
    public $requiredField;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Required;

#[\Attribute]
final class RequiredValue
{
    public function __construct(public $requiredField)
    {
    }
}

?>

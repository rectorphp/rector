<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\FixtureShouldNotRemoveAnnotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @annotation
 */
final class RequiredKeepOld
{
    /** @Required */
    public $requiredField;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\FixtureShouldNotRemoveAnnotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @annotation
 */
#[\Attribute]
final class RequiredKeepOld
{
    /** @Required */
    public $requiredField;
    public function __construct($requiredField)
    {
        $this->requiredField = $requiredField;
    }
}

?>

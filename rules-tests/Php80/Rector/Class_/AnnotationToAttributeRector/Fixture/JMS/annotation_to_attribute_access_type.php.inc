<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\JMS;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\AccessType("public_method")
 */
class MyClass
{
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\JMS;

use JMS\Serializer\Annotation as JMS;

#[JMS\AccessType(type: 'public_method')]
class MyClass
{
}
?>

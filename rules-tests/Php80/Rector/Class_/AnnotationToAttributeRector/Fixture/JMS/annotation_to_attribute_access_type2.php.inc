<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\JMS;

/**
 * @\JMS\Serializer\Annotation\AccessType("public_method")
 */
class MyClass2
{
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\JMS;

#[\JMS\Serializer\Annotation\AccessType(type: 'public_method')]
class MyClass2
{
}
?>

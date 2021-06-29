<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Path;

/**
 * @Path("/")
 */
final class FromImplicitToName
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Path;

#[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Path(path: '/')]
final class FromImplicitToName
{
}

?>

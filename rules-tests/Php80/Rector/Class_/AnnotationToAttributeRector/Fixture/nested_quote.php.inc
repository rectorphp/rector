<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

/**
 * @GenericAnnotation("
 *     summary: key
 *     description: 'something `id => name`.'
 * ")
 */
final class NestedQuote
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

#[GenericAnnotation(some: '
    summary: key
    description: \'something `id => name`.\'
')]
final class NestedQuote
{
}

?>

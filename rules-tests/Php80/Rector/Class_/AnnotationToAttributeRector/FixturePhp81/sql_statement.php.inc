<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

/**
 * @GenericAnnotation(filters={
 *      @GenericAnnotation(sql="
 *          $this.id IN(
 *              SELECT id
 *              FROM foo
 *              WHERE bar = 'baz'
 *          )"
 *      )
 * })
 */
class SqlStatement
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

#[GenericAnnotation(filters: [new GenericAnnotation(sql: '
         $this.id IN(
             SELECT id
             FROM foo
             WHERE bar = \'baz\'
         )')])]
class SqlStatement
{
}

?>

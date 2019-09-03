<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * This is here
 */
class DifferentNamespace
{
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * This is here
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Source\DifferentNamespaceTest
 */
class DifferentNamespace
{
}

?>

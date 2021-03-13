<?php

namespace Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @see \SomeRandom\MissingTest
 */
final class RemoveNonExistingSee
{
}

class RemoveNonExistingSeeTest
{
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @see \Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture\RemoveNonExistingSeeTest
 */
final class RemoveNonExistingSee
{
}

class RemoveNonExistingSeeTest
{
}

?>

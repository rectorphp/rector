<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

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

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture\RemoveNonExistingSeeTest
 */
final class RemoveNonExistingSee
{
}

class RemoveNonExistingSeeTest
{
}

?>

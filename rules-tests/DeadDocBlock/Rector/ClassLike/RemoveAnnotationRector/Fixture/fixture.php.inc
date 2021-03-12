<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector\Fixture;

/**
 * @method getName()
 */
final class Fixture
{
}

?>
-----
<?php

namespace Rector\Tests\DeadDocBlock\Rector\ClassLike\RemoveAnnotationRector\Fixture;

final class Fixture
{
}

?>

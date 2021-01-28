<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

/**
 * @method getName()
 */
final class Fixture
{
}

?>
-----
<?php

namespace Rector\DeadDocBlock\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

final class Fixture
{
}

?>

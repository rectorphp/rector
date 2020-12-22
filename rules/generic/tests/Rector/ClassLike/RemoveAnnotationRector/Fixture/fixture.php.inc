<?php

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

/**
 * @method getName()
 */
final class Fixture
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

final class Fixture
{
}

?>

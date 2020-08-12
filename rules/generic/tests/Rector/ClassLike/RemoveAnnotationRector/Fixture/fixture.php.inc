<?php

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

/**
 * @method getName()
 */
final class SomeClass
{
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveAnnotationRector\Fixture;

final class SomeClass
{
}

?>

<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;

/**
 * @property OldClass $some
 * @property-read OldClass $someRead
 * @property-write OldClass $someWrite
 */
class RenamePropertyRead
{
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;

/**
 * @property \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass $some
 * @property-read \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass $someRead
 * @property-write \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass $someWrite
 */
class RenamePropertyRead
{
}

?>

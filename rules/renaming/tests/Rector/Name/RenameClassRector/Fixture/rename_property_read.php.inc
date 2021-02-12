<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass;

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

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass;

/**
 * @property \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass $some
 * @property-read \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass $someRead
 * @property-write \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass $someWrite
 */
class RenamePropertyRead
{
}

?>

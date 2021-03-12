<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;

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

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;

/**
 * @property \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass $some
 * @property-read \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass $someRead
 * @property-write \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass $someWrite
 */
class RenamePropertyRead
{
}

?>

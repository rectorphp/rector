<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\ComplexFixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassWithMethod;

$class = new OldClassWithMethod();
$class->someMethod();

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\ComplexFixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClassWithNewMethod;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClassWithMethod;

$class = new NewClassWithNewMethod();
$class->someNewMethod();

?>

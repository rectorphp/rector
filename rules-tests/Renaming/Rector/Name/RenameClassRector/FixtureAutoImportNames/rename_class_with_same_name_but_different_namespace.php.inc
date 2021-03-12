<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass;

$someService = new SomeServiceClass();

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;

$someService = new SomeServiceClass();

?>

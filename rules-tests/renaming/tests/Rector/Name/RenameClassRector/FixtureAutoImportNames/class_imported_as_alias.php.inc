<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\FirstNamespace\FirstOriginalClass as AliasedClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\SecondNamespace;

$aliasedClass = new AliasedClass();
$secondClass = new SecondNamespace\SecondOriginalClass();

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\SecondNamespace\SecondOriginalClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\FirstNamespace\FirstOriginalClass as AliasedClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\SecondNamespace;

$aliasedClass = new AliasedClass();
$secondClass = new SecondOriginalClass();

?>

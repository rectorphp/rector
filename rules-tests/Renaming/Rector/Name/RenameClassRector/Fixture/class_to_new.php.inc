<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

class MyCustomValidatorTest extends \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass
{
    // ...
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

class MyCustomValidatorTest extends \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass
{
    // ...
}

?>

<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass
{
    // ...
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass
{
    // ...
}

?>

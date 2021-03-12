<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass
{
    // ...
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass
{
    // ...
}

?>

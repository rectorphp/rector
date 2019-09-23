<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass
{
    // ...
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass
{
    // ...
}

?>

<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass
{
    // ...
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector;

class MyCustomValidatorTest extends \Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass
{
    // ...
}

?>

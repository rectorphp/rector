<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector;

class MyCustomValidatorTest extends \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\OldClass
{
    // ...
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector;

class MyCustomValidatorTest extends \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClass
{
    // ...
}

?>

<?php

namespace SomeNamespace;

class MyCustomValidatorTest extends OldClass
{
    // ...
}

class OldClass
{

}

?>
-----
<?php

namespace SomeNamespace;

class MyCustomValidatorTest extends \Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClass
{
    // ...
}

class OldClass
{

}

?>

<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

class ClassMethodDeclaration
{
    function __doSomething() {}
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

class ClassMethodDeclaration
{
    function doSomething() {}
}

?>

<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

class ClassMethodDeclaration
{
    function __doSomething() {}
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\Fixture;

class ClassMethodDeclaration
{
    function doSomething() {}
}

?>

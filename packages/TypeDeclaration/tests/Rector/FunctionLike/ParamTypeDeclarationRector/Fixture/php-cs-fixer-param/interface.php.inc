<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

class Bar {}

interface Foo
{
    /** @param Bar $bar */
    function my_foo($bar);
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

class Bar {}

interface Foo
{
    /** @param Bar $bar */
    function my_foo(\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_\Bar $bar);
}

?>

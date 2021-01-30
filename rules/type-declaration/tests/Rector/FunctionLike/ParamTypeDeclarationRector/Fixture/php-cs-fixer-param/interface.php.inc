<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

class Bar {}

interface Foo
{
    /** @param Bar $bar */
    function my_foo($bar);
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

class Bar {}

interface Foo
{
    function my_foo(\Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_\Bar $bar);
}

?>

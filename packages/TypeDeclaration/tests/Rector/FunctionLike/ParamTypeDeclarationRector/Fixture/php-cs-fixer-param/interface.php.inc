<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

interface Foo { /** @param Bar $bar */ function my_foo($bar); }
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

interface Foo { /** @param Bar $bar */ function my_foo(Bar $bar); }
?>

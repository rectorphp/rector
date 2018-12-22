<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

interface Foo { /** @param Bar $bar */ function my_foo($bar); }
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\PhpCsFixerParam\Interface_;

interface Foo { /** @param Bar $bar */ function my_foo(Bar $bar); }
?>

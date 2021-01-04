<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\SelfAccessor;

class Foo {
    /** @param self $foo */ function my_foo($foo) {}
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\SelfAccessor;

class Foo {
    /** @param self $foo */ function my_foo(self $foo) {}
}

?>

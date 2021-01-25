<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\CallableType;

/** @param callable $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\CallableType;

/** @param callable $foo */ function my_foo(callable $foo) {}
?>

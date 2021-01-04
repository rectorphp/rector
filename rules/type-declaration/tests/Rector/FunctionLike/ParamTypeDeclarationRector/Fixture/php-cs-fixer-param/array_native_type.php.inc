<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\ArrayNativeType;

/** @param array $foo */ function my_foo($foo) {}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\ArrayNativeType;

/** @param array $foo */ function my_foo(array $foo) {}
?>

<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Number;

/** @param float $bar */ function my_foo($bar) {}

/** @param int $bar */ function my_foo2($bar) {}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Number;

/** @param float $bar */ function my_foo(float $bar) {}

/** @param int $bar */ function my_foo2(int $bar) {}

?>

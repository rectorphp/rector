<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Php72;

/** @param object $foo */ function my_foo($foo) {}
/** @param null|object $foo */ function my_foo2($foo) {}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Php72;

/** @param object $foo */ function my_foo(object $foo) {}
/** @param null|object $foo */ function my_foo2(?object $foo) {}

?>

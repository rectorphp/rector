<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Nullable;

class Foo {}

/** @param null|bool $foo */ function my_foo($foo) {}
/** @param null|Foo $foo */ function my_foo2($foo) {}
/** @param null|callable $foo */ function my_foo3($foo) {}
/** @param null|Foo[] $foo */ function my_foo4($foo) {}
/** @param null|iterable $foo */ function my_foo5($foo) {}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Nullable;

class Foo {}

/** @param null|bool $foo */ function my_foo(?bool $foo) {}
/** @param null|Foo $foo */ function my_foo2(?\Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Nullable\Foo $foo) {}
/** @param null|callable $foo */ function my_foo3(?callable $foo) {}
/** @param null|Foo[] $foo */ function my_foo4(?array $foo) {}
/** @param null|iterable $foo */ function my_foo5(?iterable $foo) {}

?>

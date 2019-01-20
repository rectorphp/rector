<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Nullable;

/** @param null|bool $foo */ function my_foo($foo) {}
/** @param null|Foo $foo */ function my_foo2($foo) {}
/** @param null|callable $foo */ function my_foo3($foo) {}
/** @param null|Foo[] $foo */ function my_foo4($foo) {}
/** @param null|iterable $foo */ function my_foo5($foo) {}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\PhpCsFixerParam\Nullable;

/** @param null|bool $foo */ function my_foo(?bool $foo) {}
/** @param null|Foo $foo */ function my_foo2(?Foo $foo) {}
/** @param null|callable $foo */ function my_foo3(?callable $foo) {}
/** @param null|Foo[] $foo */ function my_foo4(?array $foo) {}
/** @param null|iterable $foo */ function my_foo5(?iterable $foo) {}

?>

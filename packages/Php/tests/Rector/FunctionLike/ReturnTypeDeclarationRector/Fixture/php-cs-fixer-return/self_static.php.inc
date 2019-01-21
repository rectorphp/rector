<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\SelfStatic;

class Foo {
    /** @return self */ function my_foo() {}
}

class Foo2 {
    /** @return static */ function my_foo() {}
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\SelfStatic;

class Foo {
    /** @return self */ function my_foo(): self {}
}

class Foo2 {
    /** @return static */ function my_foo() {}
}

?>

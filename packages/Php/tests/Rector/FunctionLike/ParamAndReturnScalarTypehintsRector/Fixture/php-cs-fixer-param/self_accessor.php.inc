<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerParam\SelfAccessor;

class Foo {
    /** @param self $foo */ function my_foo($foo) {}
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\PhpCsFixerParam\SelfAccessor;

class Foo {
    /** @param self $foo */ function my_foo(self $foo) {}
}

?>

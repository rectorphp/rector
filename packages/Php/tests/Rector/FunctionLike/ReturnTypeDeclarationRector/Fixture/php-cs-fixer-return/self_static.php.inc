<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\SelfStatic;

class Foo {
    /** @return self */
    function my_foo($value)
    {
        return $value;
    }
}

class Foo2 {
    /** @return static */
    function my_foo($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\PhpCsFixerReturn\SelfStatic;

class Foo {
    /** @return self */
    function my_foo($value): self
    {
        return $value;
    }
}

class Foo2 {
    /** @return static */
    function my_foo($value)
    {
        return $value;
    }
}

?>

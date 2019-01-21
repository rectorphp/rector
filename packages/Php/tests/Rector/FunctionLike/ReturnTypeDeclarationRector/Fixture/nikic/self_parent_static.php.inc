<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\SelfParentStatic;

class P {}

class A extends P {
    /** @return self */
    function foo($value)
    {
        return $value;
    }

    /** @return parent */
    function bar($value)
    {
        return $value;
    }

    /** @return static */
    function baz($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\SelfParentStatic;

class P {}

class A extends P {
    /** @return self */
    function foo($value): self
    {
        return $value;
    }

    /** @return parent */
    function bar($value): parent
    {
        return $value;
    }

    /** @return static */
    function baz($value)
    {
        return $value;
    }
}

?>

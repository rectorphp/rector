<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\SetTypeToCastRector\Fixture;

class SomeClass
{
    public function run($foo)
    {
        settype($foo, 'string');
        settype($foo, 'array');
        settype($foo, 'INT');
        settype($foo, 'float');
        settype($foo, 'double');
        settype($foo, 'bool');
        settype($foo, 'boolean');

        return settype($foo, 'integer');
    }

    public function null()
    {
        settype($foo, 'null');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\SetTypeToCastRector\Fixture;

class SomeClass
{
    public function run($foo)
    {
        $foo = (string) $foo;
        $foo = (array) $foo;
        $foo = (int) $foo;
        $foo = (double) $foo;
        $foo = (double) $foo;
        $foo = (bool) $foo;
        $foo = (bool) $foo;

        return (int) $foo;
    }

    public function null()
    {
        $foo = null;
    }
}

?>

<?php

namespace Rector\Tests\Rector\Typehint\ReturnTypehintRector\Wrong;

class SomeClass
{
    public function parse()
    {
    }

    public function resolve()
    {
    }

    public function nullable(): array
    {
    }

    public function clear(): array
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Typehint\ReturnTypehintRector\Wrong;

class SomeClass
{
    public function parse(): array
    {
    }

    public function resolve(): \SomeType
    {
    }

    public function nullable(): ?\SomeType
    {
    }

    public function clear()
    {
    }
}

?>

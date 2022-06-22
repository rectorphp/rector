<?php

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

class SomeStaticCall
{
    function removeReference($one)
    {
        return Foo::bar(&$one);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

class SomeStaticCall
{
    function removeReference($one)
    {
        return Foo::bar($one);
    }
}

?>

<?php

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

class SomeMethodCall
{
    function removeReference($one)
    {
        return $this->bar(&$one);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector\Fixture;

class SomeMethodCall
{
    function removeReference($one)
    {
        return $this->bar($one);
    }
}

?>

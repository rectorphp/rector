<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\MultipleProperties;

class SomeClass
{
    private $used, $unused;
    private $unused2, $unused3;

    public function getUsed()
    {
        return $this->used;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\MultipleProperties;

class SomeClass
{
    private $used;

    public function getUsed()
    {
        return $this->used;
    }
}

?>

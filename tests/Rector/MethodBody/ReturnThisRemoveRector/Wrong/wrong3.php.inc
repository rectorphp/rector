<?php

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

class SomeClassWithReturnAnnotations
{
    /**
     * @return SomeClassWithReturnAnnotations
     */
    public function someFunction()
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector;

class SomeClassWithReturnAnnotations
{
    public function someFunction()
    {
    }
}

?>

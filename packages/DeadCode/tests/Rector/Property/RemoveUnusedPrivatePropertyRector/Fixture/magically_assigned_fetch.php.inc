<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\MagicallyAssignedFetch;

class SomeClass
{
    private $seemsUnused;

    private $anotherName = 'asdf';

    public function getValue()
    {
        // but it might be used :)
        $this->{$this->anotherName} = 'foo';
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\MagicallyAssignedFetch;

class SomeClass
{
    private $anotherName = 'asdf';
    public function getValue()
    {
        // but it might be used :)
        $this->{$this->anotherName} = 'foo';
    }
}
?>

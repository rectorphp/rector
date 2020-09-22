<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class MagicallyAssignedFetch
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

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class MagicallyAssignedFetch
{
    private $anotherName = 'asdf';

    public function getValue()
    {
        // but it might be used :)
        $this->{$this->anotherName} = 'foo';
    }
}
?>

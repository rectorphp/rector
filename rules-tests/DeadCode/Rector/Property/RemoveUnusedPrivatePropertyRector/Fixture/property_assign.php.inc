<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class PropertyAssign
{
    private $a;
    private $b;
    private $c;

    public function run()
    {
        $this->a = 5;

        $this->process($this->c);
    }

    public function process($property)
    {
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class PropertyAssign
{
    private $c;

    public function run()
    {
        $this->process($this->c);
    }

    public function process($property)
    {
    }
}

?>

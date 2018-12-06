<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Wrong;

class AnyClass
{
    public function reset()
    {
        if ($this->getValue()) {
            return true;
        }
        return false;
    }

    public function getValue(): ?int
    {
        return 5;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Wrong;

class AnyClass
{
    public function reset()
    {
        return $this->getValue() !== null;
    }

    public function getValue(): ?int
    {
        return 5;
    }
}

?>

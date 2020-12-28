<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\Fixture;

class Fixture
{
    /**
     * @var int|null
     */
    private $orderItem;

    /**
     * @var int|null
     */
    private $orderItemUnit;

    public function run()
    {
        if (null !== $this->orderItem) {
            return $this->orderItem;
        }

        if (null !== $this->orderItemUnit) {
            return $this->orderItemUnit;
        }

        return null;
    }

    public function runAgain()
    {
        if ($this->orderItemUnit !== null) {
            return $this->orderItemUnit;
        }

        if ($this->orderItem !== null) {
            return $this->orderItem;
        }

        return NULL;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\Fixture;

class Fixture
{
    /**
     * @var int|null
     */
    private $orderItem;

    /**
     * @var int|null
     */
    private $orderItemUnit;

    public function run()
    {
        return $this->orderItem ?? $this->orderItemUnit;
    }

    public function runAgain()
    {
        return $this->orderItemUnit ?? $this->orderItem;
    }
}

?>

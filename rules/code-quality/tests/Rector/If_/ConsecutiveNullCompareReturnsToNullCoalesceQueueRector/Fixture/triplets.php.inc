<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\Fixture;

class Triplets
{
    /**
     * @var int|null
     */
    private $first;

    /**
     * @var int|null
     */
    private $second;

    /**
     * @var int|null
     */
    private $third;

    public function run()
    {
        if (null !== $this->first) {
            return $this->first;
        }

        if (null !== $this->second) {
            return $this->second;
        }

        if (null !== $this->third) {
            return $this->third;
        }

        return null;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\Fixture;

class Triplets
{
    /**
     * @var int|null
     */
    private $first;

    /**
     * @var int|null
     */
    private $second;

    /**
     * @var int|null
     */
    private $third;

    public function run()
    {
        return ($this->first ?? $this->second) ?? $this->third;
    }
}

?>

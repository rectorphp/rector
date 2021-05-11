<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\AutowireArrayParameter\Tests\Source;

use RectorPrefix20210511\Symplify\AutowireArrayParameter\Tests\Source\Contract\FirstCollectedInterface;
use RectorPrefix20210511\Symplify\AutowireArrayParameter\Tests\Source\Contract\SecondCollectedInterface;
final class ArrayShapeCollector
{
    /**
     * @var array<FirstCollectedInterface>
     */
    private $firstCollected = [];
    /**
     * @var array<SecondCollectedInterface>
     */
    private $secondCollected = [];
    /**
     * @param array<FirstCollectedInterface> $firstCollected
     * @param array<SecondCollectedInterface> $secondCollected
     */
    public function __construct(array $firstCollected, array $secondCollected)
    {
        $this->firstCollected = $firstCollected;
        $this->secondCollected = $secondCollected;
    }
    /**
     * @return array<FirstCollectedInterface>
     */
    public function getFirstCollected() : array
    {
        return $this->firstCollected;
    }
    /**
     * @return array<SecondCollectedInterface>
     */
    public function getSecondCollected() : array
    {
        return $this->secondCollected;
    }
}

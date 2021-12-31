<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\EasyTesting\ValueObject\FixtureSplit;

final class TrioContent
{
    /**
     * @var string
     */
    private $firstValue;
    /**
     * @var string
     */
    private $secondValue;
    /**
     * @var string
     */
    private $expectedResult;
    public function __construct(string $firstValue, string $secondValue, string $expectedResult)
    {
        $this->firstValue = $firstValue;
        $this->secondValue = $secondValue;
        $this->expectedResult = $expectedResult;
    }
    public function getFirstValue() : string
    {
        return $this->firstValue;
    }
    public function getSecondValue() : string
    {
        return $this->secondValue;
    }
    public function getExpectedResult() : string
    {
        return $this->expectedResult;
    }
}

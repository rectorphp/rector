<?php
declare(strict_types=1);


namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Scalar\String_;

final class ExpectationMockCollection
{
    /**
     * @var ExpectationMock[]
     */
    private $expectationMocks = [];

    /**
     * @return ExpectationMock[]
     */
    public function getExpectationMocks(): array
    {
        return $this->expectationMocks;
    }

    public function hasExpectationMocks(): bool
    {
        return count($this->expectationMocks) > 0;
    }

    public function add(ExpectationMock $expectationMock): void
    {
        $this->expectationMocks[] = $expectationMock;
    }

    public function getHighestAtIndex(): int
    {
        if (!$this->hasExpectationMocks()) {
            return 0;
        }

        $indexes = array_map(static function (ExpectationMock $expectationMock) {
            return $expectationMock->getIndex();
        }, $this->getExpectationMocks());
        return max($indexes) ?: 0;
    }

    public function getLowestAtIndex(): int
    {
        if (!$this->hasExpectationMocks()) {
            return 0;
        }

        $indexes = array_map(static function (ExpectationMock $expectationMock) {
            return $expectationMock->getIndex();
        }, $this->getExpectationMocks());
        return min($indexes) ?: 0;
    }

    public function isMissingAtIndexBetweenHighestAndLowest(): bool
    {
        $highestAtIndex = $this->getHighestAtIndex();
        $lowestAtIndex = $this->getLowestAtIndex();
        return ($highestAtIndex - $lowestAtIndex + 1) !== count($this->getExpectationMocks());
    }

    public function hasMissingAtIndexes(): bool
    {
        if ($this->getLowestAtIndex() !== 0) {
            return true;
        }

        if ($this->isMissingAtIndexBetweenHighestAndLowest()) {
            return true;
        }

        return false;
    }

    public function hasWithValues(): bool
    {
        foreach ($this->getExpectationMocks() as $expectationMock) {
            if (
                count($expectationMock->getWithArguments()) > 1
                || (
                    count($expectationMock->getWithArguments()) === 1
                    && $expectationMock->getWithArguments()[0] !== null
                )) {
                return true;
            }
        }

        return false;
    }

    public function hasReturnValues(): bool
    {
        foreach ($this->getExpectationMocks() as $expectationMock) {
            if ($expectationMock->getReturn() !== null) {
                return true;
            }
        }

        return false;
    }

    public function hasMissingReturnValues(): bool
    {
        foreach ($this->getExpectationMocks() as $expectationMock) {
            if ($expectationMock->getReturn() === null) {
                return true;
            }
        }

        return false;
    }

    public function isExpectedMethodAlwaysTheSame(): bool
    {
        $previousMethod = '';
        foreach ($this->getExpectationMocks() as $expectationMock) {
            $methodArgument = $expectationMock->getMethodArguments()[0];
            if (null !== $methodArgument && $methodArgument->value instanceof String_) {
                if ($previousMethod === '') {
                    $previousMethod = $methodArgument->value->value;
                }
                if ($previousMethod !== $methodArgument->value->value) {
                    return false;
                }
            }
        }
        return true;
    }
}

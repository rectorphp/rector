<?php

declare (strict_types=1);
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
    public function getExpectationMocks() : array
    {
        return $this->expectationMocks;
    }
    public function hasExpectationMocks() : bool
    {
        return $this->expectationMocks !== [];
    }
    public function add(\Rector\PHPUnit\ValueObject\ExpectationMock $expectationMock) : void
    {
        $this->expectationMocks[] = $expectationMock;
    }
    public function getHighestAtIndex() : int
    {
        if (!$this->hasExpectationMocks()) {
            return 0;
        }
        $indexes = \array_map(static function (\Rector\PHPUnit\ValueObject\ExpectationMock $expectationMock) : int {
            return $expectationMock->getIndex();
        }, $this->expectationMocks);
        return (int) \max($indexes);
    }
    public function getLowestAtIndex() : int
    {
        if (!$this->hasExpectationMocks()) {
            return 0;
        }
        $indexes = \array_map(static function (\Rector\PHPUnit\ValueObject\ExpectationMock $expectationMock) : int {
            return $expectationMock->getIndex();
        }, $this->expectationMocks);
        return (int) \min($indexes);
    }
    public function isMissingAtIndexBetweenHighestAndLowest() : bool
    {
        $highestAtIndex = $this->getHighestAtIndex();
        $lowestAtIndex = $this->getLowestAtIndex();
        return $highestAtIndex - $lowestAtIndex + 1 !== \count($this->expectationMocks);
    }
    public function hasMissingAtIndexes() : bool
    {
        if ($this->getLowestAtIndex() !== 0) {
            return \true;
        }
        return $this->isMissingAtIndexBetweenHighestAndLowest();
    }
    public function hasWithValues() : bool
    {
        foreach ($this->expectationMocks as $expectationMock) {
            if (\count($expectationMock->getWithArguments()) > 1) {
                return \true;
            }
            if (\count($expectationMock->getWithArguments()) !== 1) {
                continue;
            }
            if ($expectationMock->getWithArguments()[0] === null) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    public function hasReturnValues() : bool
    {
        foreach ($this->expectationMocks as $expectationMock) {
            if ($expectationMock->getReturn() !== null) {
                return \true;
            }
        }
        return \false;
    }
    public function hasMissingReturnValues() : bool
    {
        foreach ($this->expectationMocks as $expectationMock) {
            if ($expectationMock->getReturn() === null) {
                return \true;
            }
        }
        return \false;
    }
    public function isExpectedMethodAlwaysTheSame() : bool
    {
        $previousMethod = '';
        foreach ($this->expectationMocks as $expectationMock) {
            $methodArgument = $expectationMock->getMethodArguments()[0];
            if ($methodArgument->value instanceof \PhpParser\Node\Scalar\String_) {
                if ($previousMethod === '') {
                    $previousMethod = $methodArgument->value->value;
                }
                if ($previousMethod !== $methodArgument->value->value) {
                    return \false;
                }
            }
        }
        return \true;
    }
}

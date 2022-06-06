<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\ValueObject;

use RectorPrefix20220606\Rector\DeadCode\Contract\ConditionInterface;
final class BinaryToVersionCompareCondition implements ConditionInterface
{
    /**
     * @readonly
     * @var \Rector\DeadCode\ValueObject\VersionCompareCondition
     */
    private $versionCompareCondition;
    /**
     * @readonly
     * @var string
     */
    private $binaryClass;
    /**
     * @var mixed
     */
    private $expectedValue;
    /**
     * @param mixed $expectedValue
     */
    public function __construct(VersionCompareCondition $versionCompareCondition, string $binaryClass, $expectedValue)
    {
        $this->versionCompareCondition = $versionCompareCondition;
        $this->binaryClass = $binaryClass;
        $this->expectedValue = $expectedValue;
    }
    public function getVersionCompareCondition() : VersionCompareCondition
    {
        return $this->versionCompareCondition;
    }
    public function getBinaryClass() : string
    {
        return $this->binaryClass;
    }
    /**
     * @return mixed
     */
    public function getExpectedValue()
    {
        return $this->expectedValue;
    }
}

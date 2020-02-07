<?php

declare(strict_types=1);

namespace Rector\Polyfill\ValueObject;

use Rector\Polyfill\Contract\ConditionInterface;

final class BinaryToVersionCompareCondition implements ConditionInterface
{
    /**
     * @var mixed
     */
    private $expectedValue;

    /**
     * @var string
     */
    private $binaryClass;

    /**
     * @var VersionCompareCondition
     */
    private $versionCompareCondition;

    public function __construct(
        VersionCompareCondition $versionCompareCondition,
        string $binaryClass,
        $expectedValue
    ) {
        $this->versionCompareCondition = $versionCompareCondition;
        $this->binaryClass = $binaryClass;
        $this->expectedValue = $expectedValue;
    }

    public function getVersionCompareCondition(): VersionCompareCondition
    {
        return $this->versionCompareCondition;
    }

    public function getBinaryClass(): string
    {
        return $this->binaryClass;
    }

    public function getExpectedValue()
    {
        return $this->expectedValue;
    }
}

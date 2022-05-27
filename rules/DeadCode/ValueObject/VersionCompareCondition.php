<?php

declare (strict_types=1);
namespace Rector\DeadCode\ValueObject;

use Rector\DeadCode\Contract\ConditionInterface;
final class VersionCompareCondition implements \Rector\DeadCode\Contract\ConditionInterface
{
    /**
     * @readonly
     * @var int
     */
    private $firstVersion;
    /**
     * @readonly
     * @var int
     */
    private $secondVersion;
    /**
     * @readonly
     * @var string|null
     */
    private $compareSign;
    public function __construct(int $firstVersion, int $secondVersion, ?string $compareSign)
    {
        $this->firstVersion = $firstVersion;
        $this->secondVersion = $secondVersion;
        $this->compareSign = $compareSign;
    }
    public function getFirstVersion() : int
    {
        return $this->firstVersion;
    }
    public function getSecondVersion() : int
    {
        return $this->secondVersion;
    }
    public function getCompareSign() : ?string
    {
        return $this->compareSign;
    }
}

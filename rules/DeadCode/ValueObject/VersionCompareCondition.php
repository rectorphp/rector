<?php

declare(strict_types=1);

namespace Rector\DeadCode\ValueObject;

use Rector\DeadCode\Contract\ConditionInterface;

final class VersionCompareCondition implements ConditionInterface
{
    public function __construct(
        private readonly int $firstVersion,
        private readonly int $secondVersion,
        private readonly ?string $compareSign
    ) {
    }

    public function getFirstVersion(): int
    {
        return $this->firstVersion;
    }

    public function getSecondVersion(): int
    {
        return $this->secondVersion;
    }

    public function getCompareSign(): ?string
    {
        return $this->compareSign;
    }
}

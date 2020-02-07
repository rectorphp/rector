<?php

declare(strict_types=1);

namespace Rector\Polyfill\ValueObject;

use Rector\Polyfill\Contract\ConditionInterface;

final class VersionCompareCondition implements ConditionInterface
{
    /**
     * @var string
     */
    private $firstVersion;

    /**
     * @var string
     */
    private $secondVersion;

    /**
     * @var string|null
     */
    private $compareSign;

    public function __construct(string $firstVersion, string $secondVersion, ?string $compareSign)
    {
        $this->firstVersion = $firstVersion;
        $this->secondVersion = $secondVersion;
        $this->compareSign = $compareSign;
    }

    public function getFirstVersion(): string
    {
        return $this->firstVersion;
    }

    public function getSecondVersion(): string
    {
        return $this->secondVersion;
    }

    public function getCompareSign(): ?string
    {
        return $this->compareSign;
    }
}

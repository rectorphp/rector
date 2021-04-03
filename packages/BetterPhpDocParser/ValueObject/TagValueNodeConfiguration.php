<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class TagValueNodeConfiguration
{
    /**
     * @var bool
     */
    private $hasNewlineAfterOpening = false;

    /**
     * @var bool
     */
    private $hasNewlineBeforeClosing = false;

    public function __construct(bool $hasNewlineAfterOpening = false, bool $hasNewlineBeforeClosing = false)
    {
        $this->hasNewlineAfterOpening = $hasNewlineAfterOpening;
        $this->hasNewlineBeforeClosing = $hasNewlineBeforeClosing;
    }

    public function hasNewlineAfterOpening(): bool
    {
        return $this->hasNewlineAfterOpening;
    }

    public function hasNewlineBeforeClosing(): bool
    {
        return $this->hasNewlineBeforeClosing;
    }
}

<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class TagValueNodeConfiguration
{
    /**
     * @var string|null
     */
    private $originalContent;

    /**
     * @var array|null
     */
    private $orderedVisibleItems;

    /**
     * @var bool
     */
    private $hasNewlineAfterOpening;

    /**
     * @var bool
     */
    private $hasNewlineBeforeClosing;

    /**
     * @var bool
     */
    private $hasOpeningBracket;

    /**
     * @var bool
     */
    private $hasClosingBracket;

    /**
     * @var array
     */
    private $keysByQuotedStatus = [];

    /**
     * @var string|null
     */
    private $silentKey;

    /**
     * @var bool
     */
    private $isSilentKeyExplicit;

    public function __construct(
        ?string $originalContent = null,
        ?array $orderedVisibleItems = null,
        bool $hasNewlineAfterOpening = false,
        bool $hasNewlineBeforeClosing = false,
        bool $hasOpeningBracket = false,
        bool $hasClosingBracket = false,
        array $keysByQuotedStatus = [],
        ?string $silentKey = null,
        bool $isSilentKeyExplicit = true
    ) {
        $this->originalContent = $originalContent;
        $this->orderedVisibleItems = $orderedVisibleItems;
        $this->hasNewlineAfterOpening = $hasNewlineAfterOpening;
        $this->hasNewlineBeforeClosing = $hasNewlineBeforeClosing;
        $this->hasOpeningBracket = $hasOpeningBracket;
        $this->hasClosingBracket = $hasClosingBracket;
        $this->keysByQuotedStatus = $keysByQuotedStatus;
        $this->silentKey = $silentKey;
        $this->isSilentKeyExplicit = $isSilentKeyExplicit;
    }

    public function getOriginalContent(): ?string
    {
        return $this->originalContent;
    }

    public function getOrderedVisibleItems(): ?array
    {
        return $this->orderedVisibleItems;
    }

    public function hasNewlineAfterOpening(): bool
    {
        return $this->hasNewlineAfterOpening;
    }

    public function hasNewlineBeforeClosing(): bool
    {
        return $this->hasNewlineBeforeClosing;
    }

    public function hasOpeningBracket(): bool
    {
        return $this->hasOpeningBracket;
    }

    public function hasClosingBracket(): bool
    {
        return $this->hasClosingBracket;
    }

    public function getKeysByQuotedStatus(): array
    {
        return $this->keysByQuotedStatus;
    }

    public function getSilentKey(): ?string
    {
        return $this->silentKey;
    }

    public function isSilentKeyExplicit(): bool
    {
        return $this->isSilentKeyExplicit;
    }

    public function addOrderedVisibleItem(string $itemKey): void
    {
        $this->orderedVisibleItems[] = $itemKey;
    }

    public function mimic(self $tagValueNodeConfiguration): void
    {
        $this->isSilentKeyExplicit = $tagValueNodeConfiguration->isSilentKeyExplicit;
        $this->hasOpeningBracket = $tagValueNodeConfiguration->hasOpeningBracket;
        $this->hasClosingBracket = $tagValueNodeConfiguration->hasClosingBracket;
    }
}

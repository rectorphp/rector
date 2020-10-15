<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

use Nette\Utils\Strings;

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

    /**
     * @var bool
     */
    private $hasOpeningBracket = false;

    /**
     * @var bool
     */
    private $hasClosingBracket = false;

    /**
     * @var bool
     */
    private $isSilentKeyExplicit = false;

    /**
     * @var string
     */
    private $arrayEqualSign;

    /**
     * @var array<string, bool>
     */
    private $keysByQuotedStatus = [];

    /**
     * @var string|null
     */
    private $originalContent;

    /**
     * @var string[]|null
     */
    private $orderedVisibleItems;

    /**
     * @var string|null
     */
    private $silentKey;

    /**
     * @param array<string, bool> $keysByQuotedStatus
     * @param string[] $orderedVisibleItems
     */
    public function __construct(
        ?string $originalContent = null,
        ?array $orderedVisibleItems = null,
        bool $hasNewlineAfterOpening = false,
        bool $hasNewlineBeforeClosing = false,
        bool $hasOpeningBracket = false,
        bool $hasClosingBracket = false,
        array $keysByQuotedStatus = [],
        ?string $silentKey = null,
        bool $isSilentKeyExplicit = true,
        string $arrayEqualSign = ':'
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
        $this->arrayEqualSign = $arrayEqualSign;
    }

    public function getOriginalContent(): ?string
    {
        return $this->originalContent;
    }

    /**
     * @return string[]|null
     */
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

    /**
     * @return array<string, bool>
     */
    public function getKeysByQuotedStatus(): array
    {
        return $this->keysByQuotedStatus;
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

    public function isSilentKeyAndImplicit(string $key): bool
    {
        if ($key !== $this->silentKey) {
            return false;
        }

        return ! $this->isSilentKeyExplicit;
    }

    public function getArrayEqualSign(): string
    {
        return $this->arrayEqualSign;
    }

    public function originalContentContains(string $needle): bool
    {
        if ($this->originalContent === null) {
            return false;
        }

        return Strings::contains($this->originalContent, $needle);
    }
}

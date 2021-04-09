<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\DataProvider;

use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;

final class CurrentTokenIteratorProvider
{
    /**
     * @var BetterTokenIterator
     */
    private $betterTokenIterator;

    public function setBetterTokenIterator(BetterTokenIterator $betterTokenIterator): void
    {
        $this->betterTokenIterator = $betterTokenIterator;
    }

    public function provide(): BetterTokenIterator
    {
        return $this->betterTokenIterator;
    }
}

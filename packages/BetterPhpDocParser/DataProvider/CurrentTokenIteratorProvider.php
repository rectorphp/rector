<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\DataProvider;

use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
final class CurrentTokenIteratorProvider
{
    /**
     * @var BetterTokenIterator
     */
    private $betterTokenIterator;
    public function setBetterTokenIterator(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $betterTokenIterator) : void
    {
        $this->betterTokenIterator = $betterTokenIterator;
    }
    public function provide() : \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator
    {
        return $this->betterTokenIterator;
    }
}

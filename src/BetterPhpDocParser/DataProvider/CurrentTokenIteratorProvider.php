<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\DataProvider;

use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\Exception\ShouldNotHappenException;
final class CurrentTokenIteratorProvider
{
    private ?BetterTokenIterator $betterTokenIterator = null;
    public function setBetterTokenIterator(BetterTokenIterator $betterTokenIterator) : void
    {
        $this->betterTokenIterator = $betterTokenIterator;
    }
    public function provide() : BetterTokenIterator
    {
        if (!$this->betterTokenIterator instanceof BetterTokenIterator) {
            throw new ShouldNotHappenException();
        }
        return $this->betterTokenIterator;
    }
}

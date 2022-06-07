<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\DataProvider;

use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\Core\Exception\ShouldNotHappenException;
final class CurrentTokenIteratorProvider
{
    /**
     * @var \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator|null
     */
    private $betterTokenIterator;
    public function setBetterTokenIterator(BetterTokenIterator $betterTokenIterator) : void
    {
        $this->betterTokenIterator = $betterTokenIterator;
    }
    public function provide() : BetterTokenIterator
    {
        if ($this->betterTokenIterator === null) {
            throw new ShouldNotHappenException();
        }
        return $this->betterTokenIterator;
    }
}

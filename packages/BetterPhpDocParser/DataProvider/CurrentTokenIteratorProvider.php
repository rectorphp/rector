<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\DataProvider;

use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
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

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
    public function setBetterTokenIterator(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $betterTokenIterator) : void
    {
        $this->betterTokenIterator = $betterTokenIterator;
    }
    public function provide() : \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator
    {
        if ($this->betterTokenIterator === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->betterTokenIterator;
    }
}

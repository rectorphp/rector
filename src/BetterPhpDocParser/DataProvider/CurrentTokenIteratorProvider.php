<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\DataProvider;

use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\Exception\ShouldNotHappenException;
final class CurrentTokenIteratorProvider
{
    private ?TokenIterator $tokenIterator = null;
    public function setTokenIterator(TokenIterator $tokenIterator): void
    {
        $this->tokenIterator = $tokenIterator;
    }
    public function provide(): TokenIterator
    {
        if (!$this->tokenIterator instanceof TokenIterator) {
            throw new ShouldNotHappenException();
        }
        return $this->tokenIterator;
    }
}

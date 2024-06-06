<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Parser;

use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\Exception\ShouldNotHappenException;
final class BetterTokenIterator extends TokenIterator
{
    /**
     * @param array<int, mixed> $tokens
     */
    public function __construct(array $tokens, int $index = 0)
    {
        if ($tokens === []) {
            $index = 0;
        }
        parent::__construct($tokens, $index);
    }
    /**
     * @param int[] $types
     */
    public function isNextTokenTypes(array $types) : bool
    {
        foreach ($types as $type) {
            if ($this->isNextTokenType($type)) {
                return \true;
            }
        }
        return \false;
    }
    public function isTokenTypeOnPosition(int $tokenType, int $position) : bool
    {
        $tokens = $this->getTokens();
        $token = $tokens[$position] ?? null;
        if ($token === null) {
            return \false;
        }
        return $token[1] === $tokenType;
    }
    public function isNextTokenType(int $tokenType) : bool
    {
        if ($this->nextTokenType() === null) {
            return \false;
        }
        return $this->nextTokenType() === $tokenType;
    }
    public function printFromTo(int $from, int $to) : string
    {
        if ($to < $from) {
            throw new ShouldNotHappenException('Arguments are flipped');
        }
        $tokens = $this->getTokens();
        $content = '';
        foreach ($tokens as $key => $token) {
            if ($key < $from) {
                continue;
            }
            if ($key >= $to) {
                continue;
            }
            $content .= $token[0];
        }
        return $content;
    }
    public function currentPosition() : int
    {
        return $this->currentTokenIndex();
    }
    public function count() : int
    {
        return \count($this->getTokens());
    }
    /**
     * @return array<array{0: string, 1: int}>
     */
    public function partialTokens(int $start, int $end) : array
    {
        return \array_slice($this->getTokens(), $start, $end);
    }
    public function containsTokenType(int $type) : bool
    {
        foreach ($this->getTokens() as $token) {
            if ($token[1] === $type) {
                return \true;
            }
        }
        return \false;
    }
    private function nextTokenType() : ?int
    {
        $tokens = $this->getTokens();
        // does next token exist?
        $nextIndex = $this->currentPosition() + 1;
        if (!isset($tokens[$nextIndex])) {
            return null;
        }
        $this->pushSavePoint();
        $this->next();
        $nextTokenType = $this->currentTokenType();
        $this->rollback();
        return $nextTokenType;
    }
}

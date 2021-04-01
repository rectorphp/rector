<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\Parser;

use PHPStan\PhpDocParser\Parser\TokenIterator;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class BetterTokenIterator extends TokenIterator
{
    /**
     * @param int[] $types
     */
    public function isNextTokenTypes(array $types): bool
    {
        foreach ($types as $type) {
            if ($this->isNextTokenType($type)) {
                return true;
            }
        }

        return false;
    }

    public function isNextTokenType(int $tokenType): bool
    {
        if ($this->nextTokenType() === null) {
            return false;
        }

        return $this->nextTokenType() === $tokenType;
    }

    public function print(): string
    {
        $privatesAccessor = new PrivatesAccessor();
        $tokens = $privatesAccessor->getPrivateProperty($this, 'tokens');

        $content = '';
        foreach ($tokens as $token) {
            $content .= $token[0];
        }

        return $content;
    }

    public function endsWithType(int $tokenType): bool
    {
        $privatesAccessor = new PrivatesAccessor();
        $tokens = $privatesAccessor->getPrivateProperty($this, 'tokens');

        $lastToken = array_pop($tokens);
        return $lastToken[1] === $tokenType;
    }

    private function nextTokenType(): ?int
    {
        $this->pushSavePoint();

        $privatesAccessor = new PrivatesAccessor();
        $tokens = $privatesAccessor->getPrivateProperty($this, 'tokens');
        $index = $privatesAccessor->getPrivateProperty($this, 'index');

        // does next token exist?
        $nextIndex = $index + 1;
        if (! isset($tokens[$nextIndex])) {
            return null;
        }

        $this->next();
        $nextTokenType = $this->currentTokenType();
        $this->rollback();

        return $nextTokenType;
    }
}

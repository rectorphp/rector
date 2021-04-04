<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\Parser;

use PHPStan\PhpDocParser\Parser\TokenIterator;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class BetterTokenIterator extends TokenIterator
{
    /**
     * @var string
     */
    private const TOKENS = 'tokens';

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @param array<int, mixed> $tokens
     */
    public function __construct(array $tokens, int $index = 0)
    {
        parent::__construct($tokens, $index);

        $this->privatesAccessor = new PrivatesAccessor();
    }

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

    public function printFromTo(int $from, int $to): string
    {
        $tokens = $this->privatesAccessor->getPrivateProperty($this, self::TOKENS);

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

    public function print(): string
    {
        $tokens = $this->privatesAccessor->getPrivateProperty($this, self::TOKENS);

        $content = '';
        foreach ($tokens as $token) {
            $content .= $token[0];
        }

        return $content;
    }

    public function nextTokenType(): ?int
    {
        $this->pushSavePoint();

        $tokens = $this->privatesAccessor->getPrivateProperty($this, self::TOKENS);
        $index = $this->privatesAccessor->getPrivateProperty($this, 'index');

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

    public function currentPosition(): int
    {
        return $this->privatesAccessor->getPrivateProperty($this, 'index');
    }
}

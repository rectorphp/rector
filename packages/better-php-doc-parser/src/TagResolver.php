<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

final class TagResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/HlGzME/1
     */
    private const TAG_REGEX = '#@(var|param|return|throws|property|deprecated)#';

    /**
     * @var TagToPhpDocNodeFactoryMatcher
     */
    private $tagToPhpDocNodeFactoryMatcher;

    public function __construct(TagToPhpDocNodeFactoryMatcher $tagToPhpDocNodeFactoryMatcher)
    {
        $this->tagToPhpDocNodeFactoryMatcher = $tagToPhpDocNodeFactoryMatcher;
    }

    /**
     * E.g. @ORM\Column → @Doctrine\ORM\Mapping\Column
     */
    public function resolveTag(SmartTokenIterator $smartTokenIterator): string
    {
        $tag = $smartTokenIterator->currentTokenValue();
        $smartTokenIterator->next();

        // basic annotation
        if (Strings::match($tag, self::TAG_REGEX)) {
            return $tag;
        }

        // is not e.g "@var "
        // join tags like "@ORM\Column" etc.
        if ($smartTokenIterator->currentTokenType() !== Lexer::TOKEN_IDENTIFIER) {
            return $tag;
        }
        $oldTag = $tag;

        $tag .= $smartTokenIterator->currentTokenValue();

        $isTagMatchedByFactories = (bool) $this->tagToPhpDocNodeFactoryMatcher->match($tag);
        if (! $isTagMatchedByFactories) {
            return $oldTag;
        }

        $smartTokenIterator->next();

        return $tag;
    }
}

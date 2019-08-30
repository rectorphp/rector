<?php declare(strict_types=1);

namespace Rector\Symfony\PhpDocParser\Extension;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocParserExtensionInterface;
use Rector\Symfony\PhpDocParser\SymfonyPhpDocTagParser;

final class SymfonyPhpDocParserExtension implements PhpDocParserExtensionInterface
{
    /**
     * @var SymfonyPhpDocTagParser
     */
    private $symfonyPhpDocTagParser;

    public function __construct(SymfonyPhpDocTagParser $symfonyPhpDocTagParser)
    {
        $this->symfonyPhpDocTagParser = $symfonyPhpDocTagParser;
    }

    public function matchTag(string $tag): bool
    {
        return (bool) Strings::match($tag, '#^@(Assert|Serializer)\\\\(.*?)$#');
    }

    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        return $this->symfonyPhpDocTagParser->parse($tokenIterator, $tag);
    }
}

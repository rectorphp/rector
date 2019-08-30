<?php declare(strict_types=1);

namespace Rector\Sensio\Extension;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocParserExtensionInterface;
use Rector\Sensio\PhpDocParser\SensioPhpDocTagParser;

final class SensioPhpDocParserExtension implements PhpDocParserExtensionInterface
{
    /**
     * @var SensioPhpDocTagParser
     */
    private $sensioPhpDocTagParser;

    public function __construct(SensioPhpDocTagParser $sensioPhpDocTagParser)
    {
        $this->sensioPhpDocTagParser = $sensioPhpDocTagParser;
    }

    public function matchTag(string $tag): bool
    {
        return (bool) Strings::match($tag, '#^@Template$#');
    }

    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        return $this->sensioPhpDocTagParser->parse($tokenIterator, $tag);
    }
}

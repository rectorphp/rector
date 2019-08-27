<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Extension;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocParserExtensionInterface;
use Rector\DoctrinePhpDocParser\PhpDocParser\OrmTagParser;

final class DoctrinePhpDocParserExtension implements PhpDocParserExtensionInterface
{
    /**
     * @var OrmTagParser
     */
    private $ormTagParser;

    public function __construct(OrmTagParser $ormTagParser)
    {
        $this->ormTagParser = $ormTagParser;
    }

    public function matchTag(string $tag): bool
    {
        return (bool) Strings::match($tag, '#^@ORM\\\\(\w+)$#');
    }

    public function parse(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        return $this->ormTagParser->parse($tokenIterator, $tag);
    }
}

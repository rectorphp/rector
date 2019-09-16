<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Extension;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocParserExtensionInterface;
use Rector\BetterPhpDocParser\PhpDocParser\InjectPhpDocParser;

final class JMSPhpDocParserExtension implements PhpDocParserExtensionInterface
{
    /**
     * @var InjectPhpDocParser
     */
    private $injectPhpDocParser;

    public function __construct(InjectPhpDocParser $injectPhpDocParser)
    {
        $this->injectPhpDocParser = $injectPhpDocParser;
    }

    public function matchTag(string $tag): bool
    {
        if ($tag === '@Inject') {
            return true;
        }

        return (bool) Strings::match($tag, '#^@DI\\\\(\w+)$#');
    }

    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        return $this->injectPhpDocParser->parse($tokenIterator, $tag);
    }
}

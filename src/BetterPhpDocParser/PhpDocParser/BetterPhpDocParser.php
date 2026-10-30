<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use RectorPrefix202610\Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Attribute;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\NodeDecorator\ArrayItemClassNameDecorator;
use Rector\BetterPhpDocParser\NodeDecorator\ConstExprClassNameDecorator;
use Rector\BetterPhpDocParser\NodeDecorator\DoctrineAnnotationDecorator;
use Rector\BetterPhpDocParser\NodeDecorator\PhpDocTagGenericUsesDecorator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser
{
    /**
     * @readonly
     */
    private PhpDocParser $phpDocParser;
    /**
     * @see https://regex101.com/r/JOKSmr/5
     * @var string
     */
    private const MULTI_NEW_LINES_REGEX = '#(?<new_line>\r\n|\n){2,}#';
    /**
     * @var PhpDocNodeDecoratorInterface[]
     * @readonly
     */
    private array $phpDocNodeDecorators;
    public function __construct(PhpDocParser $phpDocParser, ConstExprClassNameDecorator $constExprClassNameDecorator, DoctrineAnnotationDecorator $doctrineAnnotationDecorator, ArrayItemClassNameDecorator $arrayItemClassNameDecorator, PhpDocTagGenericUsesDecorator $phpDocTagGenericUsesDecorator)
    {
        $this->phpDocParser = $phpDocParser;
        // The decorator order below is significant; keep it as is. DoctrineAnnotationDecorator must
        // run before ArrayItemClassNameDecorator, which resolves class names inside the array items the
        // former produces. A wrong order silently drops annotation class usages and strips their imports.
        // They are injected explicitly, not autodiscovered, so the order stays under our control.
        $this->phpDocNodeDecorators = [$constExprClassNameDecorator, $doctrineAnnotationDecorator, $arrayItemClassNameDecorator, $phpDocTagGenericUsesDecorator];
    }
    public function parseWithNode(TokenIterator $tokenIterator, Node $node): PhpDocNode
    {
        $phpDocNode = $this->phpDocParser->parse($tokenIterator);
        foreach ($phpDocNode->children as $phpDocChildNode) {
            $this->setStartAndEnd($phpDocChildNode);
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            $this->setStartAndEnd($phpDocChildNode->value);
            if ($phpDocChildNode->value instanceof GenericTagValueNode) {
                $phpDocChildNode->value->value = Strings::replace($phpDocChildNode->value->value, self::MULTI_NEW_LINES_REGEX, static fn(array $match): string => (string) $match['new_line']);
            }
        }
        foreach ($this->phpDocNodeDecorators as $phpDocNodeDecorator) {
            $phpDocNodeDecorator->decorate($phpDocNode, $node);
        }
        return $phpDocNode;
    }
    /**
     * The Doctrine annotation decorator moves these positions to the nodes it creates
     */
    private function setStartAndEnd(PhpDocParserNode $phpDocParserNode): void
    {
        $startIndex = $phpDocParserNode->getAttribute(Attribute::START_INDEX);
        $endIndex = $phpDocParserNode->getAttribute(Attribute::END_INDEX);
        if (!is_int($startIndex) || !is_int($endIndex)) {
            return;
        }
        // an empty node has no tokens, its end index is before its start index
        $phpDocParserNode->setAttribute(PhpDocAttributeKey::START_AND_END, new StartAndEnd($startIndex, max($startIndex, $endIndex + 1)));
    }
}

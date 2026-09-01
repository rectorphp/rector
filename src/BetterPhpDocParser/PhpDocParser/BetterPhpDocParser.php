<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use RectorPrefix202609\Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\NodeDecorator\ArrayItemClassNameDecorator;
use Rector\BetterPhpDocParser\NodeDecorator\ConstExprClassNameDecorator;
use Rector\BetterPhpDocParser\NodeDecorator\DoctrineAnnotationDecorator;
use Rector\BetterPhpDocParser\NodeDecorator\PhpDocTagGenericUsesDecorator;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Exception\ShouldNotHappenException;
use Rector\Util\Reflection\PrivatesAccessor;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @readonly
     */
    private TokenIteratorFactory $tokenIteratorFactory;
    /**
     * @readonly
     */
    private PrivatesAccessor $privatesAccessor;
    /**
     * @see https://regex101.com/r/JDzr0c/1
     * @var string
     */
    private const NEW_LINE_REGEX = "#(?<new_line>\r\n|\n)#";
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
    public function __construct(ParserConfig $parserConfig, TypeParser $typeParser, ConstExprParser $constExprParser, TokenIteratorFactory $tokenIteratorFactory, ConstExprClassNameDecorator $constExprClassNameDecorator, DoctrineAnnotationDecorator $doctrineAnnotationDecorator, ArrayItemClassNameDecorator $arrayItemClassNameDecorator, PhpDocTagGenericUsesDecorator $phpDocTagGenericUsesDecorator, PrivatesAccessor $privatesAccessor)
    {
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->privatesAccessor = $privatesAccessor;
        // The decorator order below is significant; keep it as is. DoctrineAnnotationDecorator must
        // run before ArrayItemClassNameDecorator, which resolves class names inside the array items the
        // former produces. A wrong order silently drops annotation class usages and strips their imports.
        // They are injected explicitly, not autodiscovered, so the order stays under our control.
        $this->phpDocNodeDecorators = [$constExprClassNameDecorator, $doctrineAnnotationDecorator, $arrayItemClassNameDecorator, $phpDocTagGenericUsesDecorator];
        parent::__construct(
            // ParserConfig
            $parserConfig,
            // TypeParser
            $typeParser,
            // ConstExprParser
            $constExprParser
        );
    }
    public function parseWithNode(BetterTokenIterator $betterTokenIterator, Node $node): PhpDocNode
    {
        $betterTokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);
        $betterTokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $children = [];
        if (!$betterTokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
            $children[] = $this->parseChildAndStoreItsPositions($betterTokenIterator);
            while ($betterTokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL) && !$betterTokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
                $children[] = $this->parseChildAndStoreItsPositions($betterTokenIterator);
            }
        }
        // might be in the middle of annotations
        $betterTokenIterator->tryConsumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);
        $phpDocNode = new PhpDocNode($children);
        foreach ($this->phpDocNodeDecorators as $phpDocNodeDecorator) {
            $phpDocNodeDecorator->decorate($phpDocNode, $node);
        }
        return $phpDocNode;
    }
    public function parseTag(TokenIterator $tokenIterator): PhpDocTagNode
    {
        // replace generic nodes with DoctrineAnnotations
        if (!$tokenIterator instanceof BetterTokenIterator) {
            throw new ShouldNotHappenException();
        }
        $tag = $this->resolveTag($tokenIterator);
        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new PhpDocTagNode($tag, $phpDocTagValueNode);
    }
    /**
     * @param BetterTokenIterator $tokenIterator
     */
    public function parseTagValue(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        $isPrecededByHorizontalWhitespace = $tokenIterator->isPrecededByHorizontalWhitespace();
        $startPosition = $tokenIterator->currentPosition();
        $phpDocTagValueNode = parent::parseTagValue($tokenIterator, $tag);
        $endPosition = $tokenIterator->currentPosition();
        if ($isPrecededByHorizontalWhitespace && property_exists($phpDocTagValueNode, 'description')) {
            $phpDocTagValueNode->description = Strings::replace((string) $phpDocTagValueNode->description, self::NEW_LINE_REGEX, static fn(array $match): string => $match['new_line'] . ' * ');
        }
        $startAndEnd = new StartAndEnd($startPosition, $endPosition);
        $phpDocTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        if ($phpDocTagValueNode instanceof GenericTagValueNode) {
            $phpDocTagValueNode->value = Strings::replace($phpDocTagValueNode->value, self::MULTI_NEW_LINES_REGEX, static fn(array $match): string => (string) $match['new_line']);
        }
        return $phpDocTagValueNode;
    }
    /**
     * @return PhpDocTextNode|PhpDocTagNode
     */
    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): PhpDocChildNode
    {
        $betterTokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        $startPosition = $betterTokenIterator->currentPosition();
        try {
            /** @var PhpDocTextNode|PhpDocTagNode $phpDocNode */
            $phpDocNode = $this->privatesAccessor->callPrivateMethod($this, 'parseChild', [$betterTokenIterator]);
        } catch (ParserException $exception) {
            $phpDocNode = new PhpDocTextNode('');
        }
        $endPosition = $betterTokenIterator->currentPosition();
        $startAndEnd = new StartAndEnd($startPosition, $endPosition);
        $phpDocNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $phpDocNode;
    }
    private function resolveTag(BetterTokenIterator $tokenIterator): string
    {
        $tag = $tokenIterator->currentTokenValue();
        $tokenIterator->next();
        // there is a space → stop
        if ($tokenIterator->isPrecededByHorizontalWhitespace()) {
            return $tag;
        }
        // is not e.g "@var "
        // join tags like "@ORM\Column" etc.
        if (!$tokenIterator->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            return $tag;
        }
        // @todo use joinUntil("(")?
        $tag .= $tokenIterator->currentTokenValue();
        $tokenIterator->next();
        return $tag;
    }
}

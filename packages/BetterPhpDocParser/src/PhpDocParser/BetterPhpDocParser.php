<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;

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
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Data\StartEndInfo;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @var bool
     */
    private $isComment = false;

    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        AttributeAwareNodeFactory $attributeAwareNodeFactory
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->privatesCaller = new PrivatesCaller();
        $this->privatesAccessor = new PrivatesAccessor();
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }

    /**
     * @return AttributeAwarePhpDocNode|PhpDocNode
     */
    public function parse(TokenIterator $tokenIterator): PhpDocNode
    {
        $this->isComment = false;

        try {
            $tokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);
        } catch (ParserException $parserException) {
            // probably "//" start
            $this->isComment = true;
            $tokenIterator->consumeTokenType(Lexer::TOKEN_OTHER);
        }

        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);

        $children = [];

        if (! $tokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_PHPDOC)) {
            $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            while ($tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL) && ! $tokenIterator->isCurrentTokenType(
                Lexer::TOKEN_CLOSE_PHPDOC
            )) {
                $children[] = $this->parseChildAndStoreItsPositions($tokenIterator);
            }
        }

        if (! $this->isComment) {
            $tokenIterator->consumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);
        }

        $phpDocNode = new PhpDocNode(array_values($children));

        return $this->attributeAwareNodeFactory->createFromNode($phpDocNode);
    }

    public function parseTagValue(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        $tagValueNode = parent::parseTagValue($tokenIterator, $tag);

        return $this->attributeAwareNodeFactory->createFromNode($tagValueNode);
    }

    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): Node
    {
        $tokenStart = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'index');
        $node = $this->privatesCaller->callPrivateMethod($this, 'parseChild', $tokenIterator);
        $tokenEnd = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'index');

        $attributeAwareNode = $this->attributeAwareNodeFactory->createFromNode($node);
        $attributeAwareNode->setAttribute(Attribute::PHP_DOC_NODE_INFO, new StartEndInfo($tokenStart, $tokenEnd));

        $possibleMultilineText = null;
        if ($attributeAwareNode instanceof PhpDocTagNode) {
            if (property_exists($attributeAwareNode->value, 'description')) {
                $possibleMultilineText = $attributeAwareNode->value->description;
            }
        }

        if ($attributeAwareNode instanceof PhpDocTextNode) {
            $possibleMultilineText = $attributeAwareNode->text;
        }

        if ($possibleMultilineText) {
            // add original text, for keeping trimmed spaces
            $originalContent = $this->getOriginalContentFromTokenIterator($tokenIterator);

            // we try to match original content without trimmed spaces
            $currentTextPattern = '#' . preg_quote($possibleMultilineText, '#') . '#s';
            $currentTextPattern = Strings::replace($currentTextPattern, '#\s#', '\s+');
            $match = Strings::match($originalContent, $currentTextPattern);

            if (isset($match[0])) {
                $attributeAwareNode->setAttribute(Attribute::ORIGINAL_CONTENT, $match[0]);
            }
        }

        return $attributeAwareNode;
    }

    /**
     * @todo cache per tokens array hash
     */
    private function getOriginalContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        $originalTokens = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'tokens');
        $originalContent = '';

        foreach ($originalTokens as $originalToken) {
            // skip opening
            if (Strings::match($originalToken[0], '#/\*\*#')) {
                continue;
            }

            // skip closing
            if (Strings::match($originalToken[0], '#\*\/#')) {
                continue;
            }

            if (Strings::match($originalToken[0], '#^\s+\*#')) {
                $originalToken[0] = PHP_EOL;
            }

            $originalContent .= $originalToken[0];
        }

        return trim($originalContent);
    }
}

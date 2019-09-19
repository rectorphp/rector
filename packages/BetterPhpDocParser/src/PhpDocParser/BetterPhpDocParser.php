<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocParserExtensionInterface;
use Rector\BetterPhpDocParser\Data\StartEndInfo;
use Rector\BetterPhpDocParser\Printer\MultilineSpaceFormatPreserver;
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

    /**
     * @var MultilineSpaceFormatPreserver
     */
    private $multilineSpaceFormatPreserver;

    /**
     * @var PhpDocParserExtensionInterface[]
     */
    private $phpDocParserExtensions = [];

    /**
     * @param PhpDocParserExtensionInterface[] $phpDocParserExtensions
     */
    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        MultilineSpaceFormatPreserver $multilineSpaceFormatPreserver,
        array $phpDocParserExtensions = []
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->privatesCaller = new PrivatesCaller();
        $this->privatesAccessor = new PrivatesAccessor();
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->multilineSpaceFormatPreserver = $multilineSpaceFormatPreserver;
        $this->phpDocParserExtensions = $phpDocParserExtensions;
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

    public function parseTag(TokenIterator $tokenIterator): PhpDocTagNode
    {
        $tag = $tokenIterator->currentTokenValue();

        $tokenIterator->next();

        // @todo somehow decouple to tag pre-processor
        if (Strings::match($tag, '#@(ORM|Assert|Serializer|DI|Inject)$#')) {
            if ($tag !== '@Inject') {
                $tag .= $tokenIterator->currentTokenValue();
                $tokenIterator->next();
            }
        }

        $value = $this->parseTagValue($tokenIterator, $tag);

        return new PhpDocTagNode($tag, $value);
    }

    public function parseTagValue(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        $tokenIterator->pushSavePoint();

        foreach ($this->phpDocParserExtensions as $phpDocParserExtension) {
            if (! $phpDocParserExtension->matchTag($tag)) {
                continue;
            }

            $phpDocTagValueNode = $phpDocParserExtension->parse($tokenIterator, $tag);
            if ($phpDocTagValueNode !== null) {
                $tokenIterator->dropSavePoint();
                return $phpDocTagValueNode;
            }

            $tokenIterator->rollback();
            break;
        }

        // needed for reference support in params, see https://github.com/rectorphp/rector/issues/1734
        if ($tag === '@param') {
            $tagValueNode = $this->customParseParamTagValueNode($tokenIterator);
        } else {
            $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        }

        return $this->attributeAwareNodeFactory->createFromNode($tagValueNode);
    }

    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): Node
    {
        $tokenStart = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'index');
        $node = $this->privatesCaller->callPrivateMethod($this, 'parseChild', $tokenIterator);
        $tokenEnd = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'index');

        $attributeAwareNode = $this->attributeAwareNodeFactory->createFromNode($node);
        $attributeAwareNode->setAttribute(Attribute::PHP_DOC_NODE_INFO, new StartEndInfo($tokenStart, $tokenEnd));

        $possibleMultilineText = $this->multilineSpaceFormatPreserver->resolveCurrentPhpDocNodeText(
            $attributeAwareNode
        );

        if ($possibleMultilineText) {
            // add original text, for keeping trimmed spaces
            $originalContent = $this->getOriginalContentFromTokenIterator($tokenIterator);

            // we try to match original content without trimmed spaces
            $currentTextPattern = '#' . preg_quote($possibleMultilineText, '#') . '#s';
            $currentTextPattern = Strings::replace($currentTextPattern, '#(\s)+#', '\s+');
            $match = Strings::match($originalContent, $currentTextPattern);

            if (isset($match[0])) {
                $attributeAwareNode->setAttribute(Attribute::ORIGINAL_CONTENT, $match[0]);
            }
        }

        return $attributeAwareNode;
    }

    private function getOriginalContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        // @todo iterate through tokens...
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

    /**
     * Override of parent private method to allow reference: https://github.com/rectorphp/rector/pull/1735
     */
    private function parseParamTagValue(TokenIterator $tokenIterator): ParamTagValueNode
    {
        $typeParser = $this->privatesAccessor->getPrivateProperty($this, 'typeParser');

        $type = $typeParser->parse($tokenIterator);

        $isVariadic = $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_VARIADIC);

        // extra value over parent
        $isReference = $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_REFERENCE);

        $parameterName = $this->privatesCaller->callPrivateMethod($this, 'parseRequiredVariableName', $tokenIterator);
        $description = $this->privatesCaller->callPrivateMethod($this, 'parseOptionalDescription', $tokenIterator);

        return new AttributeAwareParamTagValueNode($type, $isVariadic, $parameterName, $description, $isReference);
    }

    private function customParseParamTagValueNode(TokenIterator $tokenIterator): PhpDocTagValueNode
    {
        try {
            $tokenIterator->pushSavePoint();
            $tagValue = $this->parseParamTagValue($tokenIterator);
            $tokenIterator->dropSavePoint();

            return $tagValue;
        } catch (ParserException $parserException) {
            $tokenIterator->rollback();
            $description = $this->privatesCaller->callPrivateMethod(
                $this,
                'parseOptionalDescription',
                $tokenIterator
            );

            return new InvalidTagValueNode($description, $parserException);
        }
    }
}

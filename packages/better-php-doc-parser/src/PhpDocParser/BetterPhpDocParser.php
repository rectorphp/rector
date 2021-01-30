<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParserAwareInterface;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\MultiPhpDocNodeFactory;
use Rector\BetterPhpDocParser\Printer\MultilineSpaceFormatPreserver;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @var string
     * @see https://regex101.com/r/HlGzME/1
     */
    private const TAG_REGEX = '#@(var|param|return|throws|property|deprecated)#';

    /**
     * @var PhpDocNodeFactoryInterface[]
     */
    private $phpDocNodeFactories = [];

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
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    /**
     * @var AnnotationContentResolver
     */
    private $annotationContentResolver;

    /**
     * @var StringTagMatchingPhpDocNodeFactoryInterface[]
     */
    private $stringTagMatchingPhpDocNodeFactories = [];

    /**
     * @param PhpDocNodeFactoryInterface[] $phpDocNodeFactories
     * @param StringTagMatchingPhpDocNodeFactoryInterface[] $stringTagMatchingPhpDocNodeFactories
     */
    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        MultilineSpaceFormatPreserver $multilineSpaceFormatPreserver,
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        AnnotationContentResolver $annotationContentResolver,
        array $phpDocNodeFactories = [],
        array $stringTagMatchingPhpDocNodeFactories = []
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->privatesCaller = new PrivatesCaller();
        $this->privatesAccessor = new PrivatesAccessor();
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->multilineSpaceFormatPreserver = $multilineSpaceFormatPreserver;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->annotationContentResolver = $annotationContentResolver;

        $this->setPhpDocNodeFactories($phpDocNodeFactories);
        $this->stringTagMatchingPhpDocNodeFactories = $stringTagMatchingPhpDocNodeFactories;
    }

    /**
     * @return AttributeAwarePhpDocNode|PhpDocNode
     */
    public function parse(TokenIterator $tokenIterator): PhpDocNode
    {
        $originalTokenIterator = clone $tokenIterator;

        $tokenIterator->consumeTokenType(Lexer::TOKEN_OPEN_PHPDOC);

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

        // might be in the middle of annotations
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_CLOSE_PHPDOC);

        $phpDocNode = new PhpDocNode(array_values($children));

        $docContent = $this->annotationContentResolver->resolveFromTokenIterator($originalTokenIterator);

        return $this->attributeAwareNodeFactory->createFromNode($phpDocNode, $docContent);
    }

    public function parseTag(TokenIterator $tokenIterator): PhpDocTagNode
    {
        $tag = $this->resolveTag($tokenIterator);

        $phpDocTagNode = $this->createPhpDocTagNodeFromStringMatch($tag, $tokenIterator);
        if ($phpDocTagNode instanceof PhpDocTagNode) {
            return $phpDocTagNode;
        }

        if ($phpDocTagNode instanceof PhpDocTagValueNode) {
            return new PhpDocTagNode($tag, $phpDocTagNode);
        }

        $phpDocTagValueNode = $this->parseTagValue($tokenIterator, $tag);
        return new PhpDocTagNode($tag, $phpDocTagValueNode);
    }

    public function parseTagValue(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        $currentPhpNode = $this->currentNodeProvider->getNode();
        if (! $currentPhpNode instanceof \PhpParser\Node) {
            throw new ShouldNotHappenException();
        }

        $tagValueNode = null;

        // class-annotation
        $phpDocNodeFactory = $this->matchTagToPhpDocNodeFactory($tag);
        if ($phpDocNodeFactory !== null) {
            $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
                $tag,
                $currentPhpNode
            );

            $tagValueNode = $phpDocNodeFactory->createFromNodeAndTokens(
                $currentPhpNode,
                $tokenIterator,
                $fullyQualifiedAnnotationClass
            );
        }

        $originalTokenIterator = clone $tokenIterator;
        $docContent = $this->annotationContentResolver->resolveFromTokenIterator($originalTokenIterator);

        // fallback to original parser
        if ($tagValueNode === null) {
            $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        }

        return $this->attributeAwareNodeFactory->createFromNode($tagValueNode, $docContent);
    }

    /**
     * @param PhpDocNodeFactoryInterface[] $phpDocNodeFactories
     */
    private function setPhpDocNodeFactories(array $phpDocNodeFactories): void
    {
        foreach ($phpDocNodeFactories as $phpDocNodeFactory) {
            $classes = $this->resolvePhpDocNodeFactoryClasses($phpDocNodeFactory);
            foreach ($classes as $class) {
                $this->phpDocNodeFactories[$class] = $phpDocNodeFactory;
            }
        }
    }

    private function parseChildAndStoreItsPositions(TokenIterator $tokenIterator): Node
    {
        $originalTokenIterator = clone $tokenIterator;
        $docContent = $this->annotationContentResolver->resolveFromTokenIterator($originalTokenIterator);

        $tokenStart = $this->getTokenIteratorIndex($tokenIterator);
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', [$tokenIterator]);

        $tokenEnd = $this->resolveTokenEnd($tokenIterator);

        $startAndEnd = new StartAndEnd($tokenStart, $tokenEnd);

        $attributeAwareNode = $this->attributeAwareNodeFactory->createFromNode($phpDocNode, $docContent);
        $attributeAwareNode->setAttribute(Attribute::START_END, $startAndEnd);

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

    private function resolveTag(TokenIterator $tokenIterator): string
    {
        $tag = $tokenIterator->currentTokenValue();

        $tokenIterator->next();

        // basic annotation
        if (Strings::match($tag, self::TAG_REGEX)) {
            return $tag;
        }

        // is not e.g "@var "
        // join tags like "@ORM\Column" etc.
        if ($tokenIterator->currentTokenType() !== Lexer::TOKEN_IDENTIFIER) {
            return $tag;
        }
        $oldTag = $tag;

        $tag .= $tokenIterator->currentTokenValue();

        $isTagMatchedByFactories = (bool) $this->matchTagToPhpDocNodeFactory($tag);
        if (! $isTagMatchedByFactories) {
            return $oldTag;
        }

        $tokenIterator->next();

        return $tag;
    }

    private function matchTagToPhpDocNodeFactory(string $tag): ?PhpDocNodeFactoryInterface
    {
        $currentPhpNode = $this->currentNodeProvider->getNode();
        if (! $currentPhpNode instanceof \PhpParser\Node) {
            throw new ShouldNotHappenException();
        }

        $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
            $tag,
            $currentPhpNode
        );

        return $this->phpDocNodeFactories[$fullyQualifiedAnnotationClass] ?? null;
    }

    /**
     * @return string[]
     */
    private function resolvePhpDocNodeFactoryClasses(PhpDocNodeFactoryInterface $phpDocNodeFactory): array
    {
        if ($phpDocNodeFactory instanceof SpecificPhpDocNodeFactoryInterface) {
            return $phpDocNodeFactory->getClasses();
        }

        if ($phpDocNodeFactory instanceof MultiPhpDocNodeFactory) {
            return $phpDocNodeFactory->getTagValueNodeClassesToAnnotationClasses();
        }

        throw new ShouldNotHappenException();
    }

    private function getTokenIteratorIndex(TokenIterator $tokenIterator): int
    {
        return (int) $this->privatesAccessor->getPrivateProperty($tokenIterator, 'index');
    }

    private function resolveTokenEnd(TokenIterator $tokenIterator): int
    {
        $tokenEnd = $this->getTokenIteratorIndex($tokenIterator);

        return $this->adjustTokenEndToFitClassAnnotation($tokenIterator, $tokenEnd);
    }

    private function getOriginalContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        $originalTokens = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'tokens');
        $originalContent = '';

        foreach ($originalTokens as $originalToken) {
            // skip opening
            if ($originalToken[1] === Lexer::TOKEN_OPEN_PHPDOC) {
                continue;
            }

            // skip closing
            if ($originalToken[1] === Lexer::TOKEN_CLOSE_PHPDOC) {
                continue;
            }

            if ($originalToken[1] === Lexer::TOKEN_PHPDOC_EOL) {
                $originalToken[0] = PHP_EOL;
            }

            $originalContent .= $originalToken[0];
        }

        return trim($originalContent);
    }

    /**
     * @see https://github.com/rectorphp/rector/issues/2158
     *
     * Need to find end of this bracket first, because the parseChild() skips class annotatinos
     */
    private function adjustTokenEndToFitClassAnnotation(TokenIterator $tokenIterator, int $tokenEnd): int
    {
        $tokens = $this->privatesAccessor->getPrivateProperty($tokenIterator, 'tokens');
        if ($tokens[$tokenEnd][0] !== '(') {
            return $tokenEnd;
        }

        while ($tokens[$tokenEnd][0] !== ')') {
            ++$tokenEnd;

            // to prevent missing index error
            if (! isset($tokens[$tokenEnd])) {
                return --$tokenEnd;
            }
        }

        ++$tokenEnd;

        return $tokenEnd;
    }

    private function createPhpDocTagNodeFromStringMatch(string $tag, TokenIterator $tokenIterator): ?Node
    {
        foreach ($this->stringTagMatchingPhpDocNodeFactories as $stringTagMatchingPhpDocNodeFactory) {
            if (! $stringTagMatchingPhpDocNodeFactory->match($tag)) {
                continue;
            }

            if ($stringTagMatchingPhpDocNodeFactory instanceof PhpDocParserAwareInterface) {
                $stringTagMatchingPhpDocNodeFactory->setPhpDocParser($this);
            }

            return $stringTagMatchingPhpDocNodeFactory->createFromTokens($tokenIterator);
        }

        return null;
    }
}

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
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\MultiPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParserAwareInterface;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
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
     * @var PhpDocNodeMapper
     */
    private $phpDocNodeMapper;

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
        PhpDocNodeMapper $phpDocNodeMapper,
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        AnnotationContentResolver $annotationContentResolver,
        array $phpDocNodeFactories = [],
        array $stringTagMatchingPhpDocNodeFactories = []
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->setPhpDocNodeFactories($phpDocNodeFactories);

        $this->privatesCaller = new PrivatesCaller();
        $this->privatesAccessor = new PrivatesAccessor();
        $this->phpDocNodeMapper = $phpDocNodeMapper;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->stringTagMatchingPhpDocNodeFactories = $stringTagMatchingPhpDocNodeFactories;
    }

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

        return $this->phpDocNodeMapper->transform($phpDocNode, $docContent);
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
        if (! $tagValueNode instanceof PhpDocTagValueNode) {
            $tagValueNode = parent::parseTagValue($tokenIterator, $tag);
        }

        return $this->phpDocNodeMapper->transform($tagValueNode, $docContent);
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

        /** @var PhpDocNode $phpDocNode */
        $phpDocNode = $this->privatesCaller->callPrivateMethod($this, 'parseChild', [$tokenIterator]);

        $tokenEnd = $this->resolveTokenEnd($tokenIterator);

        $startAndEnd = new StartAndEnd($tokenStart, $tokenEnd);

        $transformedPhpDocNode = $this->phpDocNodeMapper->transform($phpDocNode, $docContent);
        $transformedPhpDocNode->setAttribute(Attribute::START_END, $startAndEnd);

        return $transformedPhpDocNode;
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
        if ($phpDocNodeFactory instanceof MultiPhpDocNodeFactoryInterface) {
            return $phpDocNodeFactory->getTagValueNodeClassesToAnnotationClasses();
        }

        if ($phpDocNodeFactory instanceof SpecificPhpDocNodeFactoryInterface) {
            return $phpDocNodeFactory->getClasses();
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

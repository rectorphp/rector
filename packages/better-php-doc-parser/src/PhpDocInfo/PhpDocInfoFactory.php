<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PhpDocInfoFactory
{
    /**
     * @var PhpDocParser
     */
    private $betterPhpDocParser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    /**
     * @var AnnotationNaming
     */
    private $annotationNaming;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    public function __construct(
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        CurrentNodeProvider $currentNodeProvider,
        Lexer $lexer,
        BetterPhpDocParser $betterPhpDocParser,
        StaticTypeMapper $staticTypeMapper,
        AnnotationNaming $annotationNaming,
        RectorChangeCollector $rectorChangeCollector
    ) {
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->lexer = $lexer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->annotationNaming = $annotationNaming;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }

    public function createFromNodeOrEmpty(Node $node): PhpDocInfo
    {
        // already added
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof PhpDocInfo) {
            return $phpDocInfo;
        }

        $phpDocInfo = $this->createFromNode($node);
        if ($phpDocInfo instanceof PhpDocInfo) {
            return $phpDocInfo;
        }

        return $this->createEmpty($node);
    }

    public function createFromNode(Node $node): ?PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $docComment = $node->getDocComment();
        if (! $docComment instanceof Doc) {
            if ($node->getComments() !== []) {
                return null;
            }

            // create empty node
            $content = '';
            $tokens = [];
            $phpDocNode = new AttributeAwarePhpDocNode([]);
        } else {
            $content = $docComment->getText();
            $tokens = $this->lexer->tokenize($content);
            try {
                $phpDocNode = $this->parseTokensToPhpDocNode($tokens);
            } catch (ParserException $parserException) {
                return null;
            }

            $this->setPositionOfLastToken($phpDocNode);
        }

        return $this->createFromPhpDocNode($phpDocNode, $content, $tokens, $node);
    }

    public function createEmpty(Node $node): PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $attributeAwarePhpDocNode = new AttributeAwarePhpDocNode([]);

        return $this->createFromPhpDocNode($attributeAwarePhpDocNode, '', [], $node);
    }

    /**
     * @param mixed[][] $tokens
     */
    private function parseTokensToPhpDocNode(array $tokens): AttributeAwarePhpDocNode
    {
        $tokenIterator = new TokenIterator($tokens);

        return $this->betterPhpDocParser->parse($tokenIterator);
    }

    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(AttributeAwarePhpDocNode $attributeAwarePhpDocNode): void
    {
        if ($attributeAwarePhpDocNode->children === []) {
            return;
        }

        $phpDocChildNodes = $attributeAwarePhpDocNode->children;
        /** @var AttributeAwareNodeInterface $lastChildNode */
        $lastChildNode = array_pop($phpDocChildNodes);

        /** @var StartAndEnd $startAndEnd */
        $startAndEnd = $lastChildNode->getAttribute(Attribute::START_END);

        if ($startAndEnd !== null) {
            $attributeAwarePhpDocNode->setAttribute(Attribute::LAST_TOKEN_POSITION, $startAndEnd->getEnd());
        }
    }

    /**
     * @param mixed[] $tokens
     */
    private function createFromPhpDocNode(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        string $content,
        array $tokens,
        Node $node
    ): PhpDocInfo {
        /** @var AttributeAwarePhpDocNode $attributeAwarePhpDocNode */
        $attributeAwarePhpDocNode = $this->attributeAwareNodeFactory->createFromNode(
            $attributeAwarePhpDocNode,
            $content
        );

        $phpDocInfo = new PhpDocInfo(
            $attributeAwarePhpDocNode,
            $tokens,
            $content,
            $this->staticTypeMapper,
            $node,
            $this->annotationNaming,
            $this->currentNodeProvider,
            $this->rectorChangeCollector
        );

        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }
}

<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
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
     * @var PhpDocNodeMapper
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

    /**
     * @var array<string, PhpDocInfo>
     */
    private $phpDocInfosByObjectHash = [];

    public function __construct(
        PhpDocNodeMapper $attributeAwareNodeFactory,
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
        $objectHash = spl_object_hash($node);
        if (isset($this->phpDocInfosByObjectHash[$objectHash])) {
            return $this->phpDocInfosByObjectHash[$objectHash];
        }

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
            $phpDocNode = new PhpDocNode([]);
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

        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, $content, $tokens, $node);
        $this->phpDocInfosByObjectHash[$objectHash] = $phpDocInfo;

        return $phpDocInfo;
    }

    public function createEmpty(Node $node): PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $phpDocNode = new PhpDocNode([]);
        return $this->createFromPhpDocNode($phpDocNode, '', [], $node);
    }

    /**
     * @param mixed[][] $tokens
     */
    private function parseTokensToPhpDocNode(array $tokens): PhpDocNode
    {
        $tokenIterator = new TokenIterator($tokens);

        return $this->betterPhpDocParser->parse($tokenIterator);
    }

    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(PhpDocNode $phpDocNode): void
    {
        if ($phpDocNode->children === []) {
            return;
        }

        $phpDocChildNodes = $phpDocNode->children;
        $lastChildNode = array_pop($phpDocChildNodes);

        /** @var StartAndEnd $startAndEnd */
        $startAndEnd = $lastChildNode->getAttribute(Attribute::START_END);

        if ($startAndEnd !== null) {
            $phpDocNode->setAttribute(Attribute::LAST_TOKEN_POSITION, $startAndEnd->getEnd());
        }
    }

    /**
     * @param mixed[] $tokens
     */
    private function createFromPhpDocNode(
        PhpDocNode $phpDocNode,
        string $content,
        array $tokens,
        Node $node
    ): PhpDocInfo {
        /** @var PhpDocNode $phpDocNode */
        $phpDocNode = $this->attributeAwareNodeFactory->transform($phpDocNode, $content);

        $phpDocInfo = new PhpDocInfo(
            $phpDocNode,
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

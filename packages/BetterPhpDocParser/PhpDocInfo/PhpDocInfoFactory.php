<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
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
    private $phpDocNodeMapper;

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
        PhpDocNodeMapper $phpDocNodeMapper,
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
        $this->phpDocNodeMapper = $phpDocNodeMapper;
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
            $tokenIterator = new BetterTokenIterator([]);
            $phpDocNode = new PhpDocNode([]);
        } else {
            $content = $docComment->getText();
            $tokens = $this->lexer->tokenize($content);
            $tokenIterator = new BetterTokenIterator($tokens);

            $phpDocNode = $this->betterPhpDocParser->parse($tokenIterator);
            $this->setPositionOfLastToken($phpDocNode);
        }

        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, $tokenIterator, $node);
        $this->phpDocInfosByObjectHash[$objectHash] = $phpDocInfo;

        return $phpDocInfo;
    }

    public function createEmpty(Node $node): PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $phpDocNode = new PhpDocNode([]);
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, new BetterTokenIterator([]), $node);

        // multiline by default
        $phpDocInfo->makeMultiLined();

        return $phpDocInfo;
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

        $startAndEnd = $lastChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

        if ($startAndEnd instanceof StartAndEnd) {
            $phpDocNode->setAttribute(PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION, $startAndEnd->getEnd());
        }
    }

    private function createFromPhpDocNode(
        PhpDocNode $phpDocNode,
        BetterTokenIterator $betterTokenIterator,
        Node $node
    ): PhpDocInfo {
        $this->phpDocNodeMapper->transform($phpDocNode, $betterTokenIterator);

        $phpDocInfo = new PhpDocInfo(
            $phpDocNode,
            $betterTokenIterator,
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

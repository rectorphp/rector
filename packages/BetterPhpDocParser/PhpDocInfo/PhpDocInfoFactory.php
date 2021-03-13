<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\DecoratingNodeFactory;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\ValueObject\AttributeKey\AttributeKey as PhpDocAttributeKey;
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

    /**
     * @var DecoratingNodeFactory
     */
    private $decoratingNodeFactory;

    public function __construct(
        CurrentNodeProvider $currentNodeProvider,
        Lexer $lexer,
        BetterPhpDocParser $betterPhpDocParser,
        StaticTypeMapper $staticTypeMapper,
        AnnotationNaming $annotationNaming,
        DecoratingNodeFactory $decoratingNodeFactory,
        RectorChangeCollector $rectorChangeCollector
    ) {
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->lexer = $lexer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->annotationNaming = $annotationNaming;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->decoratingNodeFactory = $decoratingNodeFactory;
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

        $PhpDocNode = new PhpDocNode([]);

        return $this->createFromPhpDocNode($PhpDocNode, '', [], $node);
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

        /** @var BaseNode $lastChildNode */
        $lastChildNode = array_pop($phpDocChildNodes);

        /** @var StartAndEnd $startAndEnd */
        $startAndEnd = $lastChildNode->getAttribute(PhpDocAttributeKey::START_END);

        if ($startAndEnd !== null) {
            $phpDocNode->setAttribute(PhpDocAttributeKey::LAST_TOKEN_POSITION, $startAndEnd->getEnd());
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
        $phpDocNode = $this->decoratingNodeFactory->createFromNode($phpDocNode, $content);

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

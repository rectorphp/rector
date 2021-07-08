<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder;
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
     * @var array<string, PhpDocInfo>
     */
    private $phpDocInfosByObjectHash = [];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocNodeMapper
     */
    private $phpDocNodeMapper;
    /**
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @var \PHPStan\PhpDocParser\Lexer\Lexer
     */
    private $lexer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser
     */
    private $betterPhpDocParser;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\BetterPhpDocParser\Annotation\AnnotationNaming
     */
    private $annotationNaming;
    /**
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder
     */
    private $phpDocNodeByTypeFinder;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocNodeMapper $phpDocNodeMapper, \Rector\Core\Configuration\CurrentNodeProvider $currentNodeProvider, \PHPStan\PhpDocParser\Lexer\Lexer $lexer, \Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser $betterPhpDocParser, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\Annotation\AnnotationNaming $annotationNaming, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder $phpDocNodeByTypeFinder)
    {
        $this->phpDocNodeMapper = $phpDocNodeMapper;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->lexer = $lexer;
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->annotationNaming = $annotationNaming;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->phpDocNodeByTypeFinder = $phpDocNodeByTypeFinder;
    }
    public function createFromNodeOrEmpty(\PhpParser\Node $node) : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        // already added
        $phpDocInfo = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return $phpDocInfo;
        }
        $phpDocInfo = $this->createFromNode($node);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return $phpDocInfo;
        }
        return $this->createEmpty($node);
    }
    public function createFromNode(\PhpParser\Node $node) : ?\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $objectHash = \spl_object_hash($node);
        if (isset($this->phpDocInfosByObjectHash[$objectHash])) {
            return $this->phpDocInfosByObjectHash[$objectHash];
        }
        /** @see \Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator::decorate() */
        $this->currentNodeProvider->setNode($node);
        $docComment = $node->getDocComment();
        if (!$docComment instanceof \PhpParser\Comment\Doc) {
            if ($node->getComments() !== []) {
                return null;
            }
            // create empty node
            $tokenIterator = new \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator([]);
            $phpDocNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode([]);
        } else {
            $text = $docComment->getText();
            $tokens = $this->lexer->tokenize($text);
            $tokenIterator = new \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator($tokens);
            $phpDocNode = $this->betterPhpDocParser->parse($tokenIterator);
            $this->setPositionOfLastToken($phpDocNode);
        }
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, $tokenIterator, $node);
        $this->phpDocInfosByObjectHash[$objectHash] = $phpDocInfo;
        return $phpDocInfo;
    }
    public function createEmpty(\PhpParser\Node $node) : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        /** @see \Rector\BetterPhpDocParser\PhpDocParser\DoctrineAnnotationDecorator::decorate() */
        $this->currentNodeProvider->setNode($node);
        $phpDocNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode([]);
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, new \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator([]), $node);
        // multiline by default
        $phpDocInfo->makeMultiLined();
        return $phpDocInfo;
    }
    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode) : void
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $phpDocChildNodes = $phpDocNode->children;
        $lastChildNode = \array_pop($phpDocChildNodes);
        $startAndEnd = $lastChildNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END);
        if ($startAndEnd instanceof \Rector\BetterPhpDocParser\ValueObject\StartAndEnd) {
            $phpDocNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION, $startAndEnd->getEnd());
        }
    }
    private function createFromPhpDocNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $betterTokenIterator, \PhpParser\Node $node) : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $this->phpDocNodeMapper->transform($phpDocNode, $betterTokenIterator);
        $phpDocInfo = new \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo($phpDocNode, $betterTokenIterator, $this->staticTypeMapper, $node, $this->annotationNaming, $this->currentNodeProvider, $this->rectorChangeCollector, $this->phpDocNodeByTypeFinder);
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        return $phpDocInfo;
    }
}

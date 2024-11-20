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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PhpDocInfoFactory
{
    /**
     * @readonly
     */
    private PhpDocNodeMapper $phpDocNodeMapper;
    /**
     * @readonly
     */
    private Lexer $lexer;
    /**
     * @readonly
     */
    private BetterPhpDocParser $betterPhpDocParser;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private AnnotationNaming $annotationNaming;
    /**
     * @readonly
     */
    private PhpDocNodeByTypeFinder $phpDocNodeByTypeFinder;
    /**
     * @var array<int, PhpDocInfo>
     */
    private array $phpDocInfosByObjectId = [];
    public function __construct(PhpDocNodeMapper $phpDocNodeMapper, Lexer $lexer, BetterPhpDocParser $betterPhpDocParser, StaticTypeMapper $staticTypeMapper, AnnotationNaming $annotationNaming, PhpDocNodeByTypeFinder $phpDocNodeByTypeFinder)
    {
        $this->phpDocNodeMapper = $phpDocNodeMapper;
        $this->lexer = $lexer;
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->annotationNaming = $annotationNaming;
        $this->phpDocNodeByTypeFinder = $phpDocNodeByTypeFinder;
    }
    public function createFromNodeOrEmpty(Node $node) : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        // already added
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return $phpDocInfo;
        }
        $phpDocInfo = $this->createFromNode($node);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return $phpDocInfo;
        }
        return $this->createEmpty($node);
    }
    public function createFromNode(Node $node) : ?\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $objectId = \spl_object_id($node);
        if (isset($this->phpDocInfosByObjectId[$objectId])) {
            return $this->phpDocInfosByObjectId[$objectId];
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            if ($node->getComments() === []) {
                return null;
            }
            // create empty node
            $tokenIterator = new BetterTokenIterator([]);
            $phpDocNode = new PhpDocNode([]);
        } else {
            $tokens = $this->lexer->tokenize($docComment->getText());
            $tokenIterator = new BetterTokenIterator($tokens);
            $phpDocNode = $this->betterPhpDocParser->parseWithNode($tokenIterator, $node);
            $this->setPositionOfLastToken($phpDocNode);
        }
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, $tokenIterator, $node);
        $this->phpDocInfosByObjectId[$objectId] = $phpDocInfo;
        return $phpDocInfo;
    }
    /**
     * @api downgrade
     */
    public function createEmpty(Node $node) : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $phpDocNode = new PhpDocNode([]);
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, new BetterTokenIterator([]), $node);
        // multiline by default
        $phpDocInfo->makeMultiLined();
        return $phpDocInfo;
    }
    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(PhpDocNode $phpDocNode) : void
    {
        if ($phpDocNode->children === []) {
            return;
        }
        $phpDocChildNodes = $phpDocNode->children;
        $phpDocChildNode = \array_pop($phpDocChildNodes);
        $startAndEnd = $phpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        if ($startAndEnd instanceof StartAndEnd) {
            $phpDocNode->setAttribute(PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION, $startAndEnd->getEnd());
        }
    }
    private function createFromPhpDocNode(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator, Node $node) : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $this->phpDocNodeMapper->transform($phpDocNode, $betterTokenIterator);
        $phpDocInfo = new \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo($phpDocNode, $betterTokenIterator, $this->staticTypeMapper, $node, $this->annotationNaming, $this->phpDocNodeByTypeFinder);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        return $phpDocInfo;
    }
}

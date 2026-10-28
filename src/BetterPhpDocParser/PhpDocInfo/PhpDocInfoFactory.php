<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
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
     * @readonly
     */
    private NameScopeFactory $nameScopeFactory;
    /**
     * @var array<int, PhpDocInfo>
     */
    private array $phpDocInfosByObjectId = [];
    public function __construct(PhpDocNodeMapper $phpDocNodeMapper, Lexer $lexer, BetterPhpDocParser $betterPhpDocParser, StaticTypeMapper $staticTypeMapper, AnnotationNaming $annotationNaming, PhpDocNodeByTypeFinder $phpDocNodeByTypeFinder, NameScopeFactory $nameScopeFactory)
    {
        $this->phpDocNodeMapper = $phpDocNodeMapper;
        $this->lexer = $lexer;
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->annotationNaming = $annotationNaming;
        $this->phpDocNodeByTypeFinder = $phpDocNodeByTypeFinder;
        $this->nameScopeFactory = $nameScopeFactory;
    }
    public function createFromNodeOrEmpty(Node $node): \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
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
    public function createFromNode(Node $node): ?\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $objectId = spl_object_id($node);
        if (isset($this->phpDocInfosByObjectId[$objectId])) {
            return $this->phpDocInfosByObjectId[$objectId];
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            if ($node->getComments() === []) {
                return null;
            }
            // create empty node
            $tokenIterator = new TokenIterator([]);
            $phpDocNode = new PhpDocNode([]);
        } else {
            $tokens = $this->lexer->tokenize($docComment->getText());
            $tokenIterator = new TokenIterator($tokens);
            $phpDocNode = $this->betterPhpDocParser->parseWithNode($tokenIterator, $node);
        }
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, $tokenIterator, $node);
        $this->phpDocInfosByObjectId[$objectId] = $phpDocInfo;
        return $phpDocInfo;
    }
    public function createEmpty(Node $node): \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $phpDocNode = new PhpDocNode([]);
        $phpDocInfo = $this->createFromPhpDocNode($phpDocNode, new TokenIterator([]), $node);
        // multiline by default
        $phpDocInfo->makeMultiLined();
        return $phpDocInfo;
    }
    private function createFromPhpDocNode(PhpDocNode $phpDocNode, TokenIterator $tokenIterator, Node $node): \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        $phpDocInfo = new \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo($this->phpDocNodeMapper->transform($phpDocNode), $phpDocNode, $tokenIterator, $this->staticTypeMapper, $node, $this->annotationNaming, $this->phpDocNodeByTypeFinder, $this->nameScopeFactory);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        return $phpDocInfo;
    }
}

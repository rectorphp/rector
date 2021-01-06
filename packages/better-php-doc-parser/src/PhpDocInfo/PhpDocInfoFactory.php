<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocRemover;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Parser\TokenAwarePhpDocParser;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocNode;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PhpDocInfoFactory
{
    /**
     * @var TokenAwarePhpDocParser
     */
    private $tokenAwarePhpDocParser;

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
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var PhpDocRemover
     */
    private $phpDocRemover;

    public function __construct(
        CurrentNodeProvider $currentNodeProvider,
        Lexer $lexer,
        TokenAwarePhpDocParser $tokenAwarePhpDocParser,
        PhpDocRemover $phpDocRemover,
        PhpDocTypeChanger $phpDocTypeChanger,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->tokenAwarePhpDocParser = $tokenAwarePhpDocParser;
        $this->lexer = $lexer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocRemover = $phpDocRemover;
    }

    public function createFromNodeOrEmpty(Node $node): PhpDocInfo
    {
        $phpDocInfo = $this->createFromNode($node);
        if ($phpDocInfo !== null) {
            return $phpDocInfo;
        }

        return $this->createEmpty($node);
    }

    public function createFromNode(Node $node): ?PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $docComment = $node->getDocComment();
        if ($docComment === null) {
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
            $phpDocNode = $this->parseTokensToPhpDocNode($content);
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
     * @return AttributeAwareInterface&PhpDocNode
     */
    private function parseTokensToPhpDocNode(string $docContent)
    {
        return $this->tokenAwarePhpDocParser->parseDocBlock($docContent);
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
        $phpDocInfo = new PhpDocInfo(
            $attributeAwarePhpDocNode,
            $tokens,
            $content,
            $this->staticTypeMapper,
            $node,
            $this->phpDocTypeChanger,
            $this->phpDocRemover,
        );

        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }
}

<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocRemover;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocNode;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
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
        BetterPhpDocParser $betterPhpDocParser,
        PhpDocRemover $phpDocRemover,
        PhpDocTypeChanger $phpDocTypeChanger,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->betterPhpDocParser = $betterPhpDocParser;
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
            $phpDocNode = $this->parseTokensToPhpDocNode($tokens);
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
     * @return AttributeAwareInterface&PhpDocNode
     */
    private function parseTokensToPhpDocNode(array $tokens)
    {
        $tokenIterator = new SmartTokenIterator($tokens);
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
        /** @var AttributeAwareInterface $lastChildNode */
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

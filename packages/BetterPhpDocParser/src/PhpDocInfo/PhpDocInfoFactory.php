<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\Configuration\CurrentNodeProvider;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\StaticTypeMapper;

final class PhpDocInfoFactory
{
    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

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
     * @var PhpDocInfo[]
     */
    private $phpDocInfoByObjectHash = [];

    public function __construct(
        PhpDocParser $phpDocParser,
        Lexer $lexer,
        CurrentNodeProvider $currentNodeProvider,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->phpDocParser = $phpDocParser;
        $this->lexer = $lexer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    public function createFromNode(Node $node): PhpDocInfo
    {
        $hash = $this->createUniqueDocNodeHash($node);

        if (isset($this->phpDocInfoByObjectHash[$hash])) {
            return $this->phpDocInfoByObjectHash[$hash];
        }

        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $content = $node->getDocComment()->getText();
        $tokens = $this->lexer->tokenize($content);

        /** @var AttributeAwarePhpDocNode $phpDocNode */
        $phpDocNode = $this->phpDocParser->parse(new TokenIterator($tokens));
        $phpDocNode = $this->setPositionOfLastToken($phpDocNode);

        $phpDocInfo = new PhpDocInfo($phpDocNode, $tokens, $content, $this->staticTypeMapper, $node);
        $this->phpDocInfoByObjectHash[$hash] = $phpDocInfo;

        return $phpDocInfo;
    }

    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode
    ): AttributeAwarePhpDocNode {
        if ($attributeAwarePhpDocNode->children === []) {
            return $attributeAwarePhpDocNode;
        }

        $phpDocChildNodes = $attributeAwarePhpDocNode->children;
        /** @var AttributeAwareNodeInterface $lastChildNode */
        $lastChildNode = array_pop($phpDocChildNodes);

        $phpDocNodeInfo = $lastChildNode->getAttribute(Attribute::PHP_DOC_NODE_INFO);
        if ($phpDocNodeInfo !== null) {
            $attributeAwarePhpDocNode->setAttribute(Attribute::LAST_TOKEN_POSITION, $phpDocNodeInfo->getEnd());
        }

        return $attributeAwarePhpDocNode;
    }

    private function createUniqueDocNodeHash(Node $node): string
    {
        $this->ensureNodeHasDocComment($node);

        $objectHash = spl_object_hash($node);
        $docCommentHash = spl_object_hash($node->getDocComment());
        $docCommentContentHash = sha1($node->getDocComment()->getText());

        return $objectHash . $docCommentHash . $docCommentContentHash;
    }

    private function ensureNodeHasDocComment(Node $node): void
    {
        if ($node->getDocComment() !== null) {
            return;
        }

        throw new ShouldNotHappenException(sprintf('"%s" is missing a DocComment node', get_class($node)));
    }
}

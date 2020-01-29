<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\StartEndValueObject;
use Rector\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        if ($node->hasAttribute(AttributeKey::PHP_DOC_INFO)) {
            return $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        }

        $phpDocInfo = $this->createPhpDocInfo($node);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

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

        /** @var StartEndValueObject $startEndValueObject */
        $startEndValueObject = $lastChildNode->getAttribute(Attribute::START_END);

        if ($startEndValueObject !== null) {
            $attributeAwarePhpDocNode->setAttribute(Attribute::LAST_TOKEN_POSITION, $startEndValueObject->getEnd());
        }

        return $attributeAwarePhpDocNode;
    }

    private function createPhpDocInfo(Node $node): PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $content = $node->getDocComment()->getText();
        $tokens = $this->lexer->tokenize($content);

        $tokenIterator = new TokenIterator($tokens);

        /** @var AttributeAwarePhpDocNode $phpDocNode */
        $phpDocNode = $this->phpDocParser->parse($tokenIterator);
        $phpDocNode = $this->setPositionOfLastToken($phpDocNode);

        return new PhpDocInfo($phpDocNode, $tokens, $content, $this->staticTypeMapper, $node);
    }
}

<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeDecoratorInterface;

final class PhpDocInfoFactory
{
    /**
     * @var PhpDocNodeDecoratorInterface[]
     */
    private $phpDocNodeDecoratorInterfaces = [];

    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @param PhpDocNodeDecoratorInterface[] $phpDocNodeDecoratorInterfacenodeDecorators
     */
    public function __construct(
        PhpDocParser $phpDocParser,
        Lexer $lexer,
        array $phpDocNodeDecoratorInterfacenodeDecorators
    ) {
        $this->phpDocParser = $phpDocParser;
        $this->lexer = $lexer;
        $this->phpDocNodeDecoratorInterfaces = $phpDocNodeDecoratorInterfacenodeDecorators;
    }

    public function createFromNode(Node $node): PhpDocInfo
    {
        $content = $node->getDocComment()->getText();
        $tokens = $this->lexer->tokenize($content);

        /** @var AttributeAwarePhpDocNode $phpDocNode */
        $phpDocNode = $this->phpDocParser->parse(new TokenIterator($tokens));

        foreach ($this->phpDocNodeDecoratorInterfaces as $phpDocNodeDecoratorInterface) {
            $phpDocNode = $phpDocNodeDecoratorInterface->decorate($phpDocNode, $node);
        }

        $phpDocNode = $this->setPositionOfLastToken($phpDocNode);

        return new PhpDocInfo($phpDocNode, $tokens, $content);
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

        /** @var AttributeAwareNodeInterface $lastChildNode */
        $phpDocChildNodes = $attributeAwarePhpDocNode->children;
        $lastChildNode = array_pop($phpDocChildNodes);
        $phpDocNodeInfo = $lastChildNode->getAttribute(Attribute::PHP_DOC_NODE_INFO);

        if ($phpDocNodeInfo !== null) {
            $attributeAwarePhpDocNode->setAttribute(Attribute::LAST_TOKEN_POSITION, $phpDocNodeInfo->getEnd());
        }

        return $attributeAwarePhpDocNode;
    }
}

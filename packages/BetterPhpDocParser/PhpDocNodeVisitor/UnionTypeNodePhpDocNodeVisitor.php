<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class UnionTypeNodePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;

    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof UnionTypeNode) {
            return null;
        }

        if ($node instanceof BracketsAwareUnionTypeNode) {
            return null;
        }

        // unwrap with parent array type...
        $startAndEnd = $node->getAttribute(PhpDocAttributeKey::START_AND_END);
        if (! $startAndEnd instanceof StartAndEnd) {
            throw new ShouldNotHappenException();
        }

        $betterTokenProvider = $this->currentTokenIteratorProvider->provide();
        $parent = $node->getAttribute(PhpDocAttributeKey::PARENT);

        $docContent = $this->resolveDocContent($parent, $betterTokenProvider, $startAndEnd);
        $bracketsAwareUnionTypeNode = new BracketsAwareUnionTypeNode($node->types, $docContent);
        $bracketsAwareUnionTypeNode->setAttribute(PhpDocAttributeKey::PARENT, $parent);

        return $bracketsAwareUnionTypeNode;
    }

    private function resolveDocContent(
        Node $parentNode,
        BetterTokenIterator $betterTokenProvider,
        StartAndEnd $startAndEnd
    ): string {
        if ($parentNode instanceof ArrayTypeNode) {
            $arrayTypeNodeStartAndEnd = $parentNode->getAttribute(PhpDocAttributeKey::START_AND_END);
            if ($arrayTypeNodeStartAndEnd instanceof StartAndEnd) {
                return $betterTokenProvider->printFromTo(
                    $arrayTypeNodeStartAndEnd->getStart(),
                    $arrayTypeNodeStartAndEnd->getEnd()
                );
            }
        }

        return $betterTokenProvider->printFromTo($startAndEnd->getStart(), $startAndEnd->getEnd());
    }
}

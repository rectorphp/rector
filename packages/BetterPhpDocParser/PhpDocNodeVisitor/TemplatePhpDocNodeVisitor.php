<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\SpacingAwareTemplateTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class TemplatePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
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
        if (! $node instanceof TemplateTagValueNode) {
            return null;
        }

        if ($node instanceof SpacingAwareTemplateTagValueNode) {
            return null;
        }

        $betterTokenIterator = $this->currentTokenIteratorProvider->provide();

        $startAndEnd = $node->getAttribute(PhpDocAttributeKey::START_AND_END);
        if (! $startAndEnd instanceof StartAndEnd) {
            throw new ShouldNotHappenException();
        }

        $docContent = $betterTokenIterator->printFromTo($startAndEnd->getStart(), $startAndEnd->getEnd());

        $spacingAwareTemplateTagValueNode = new SpacingAwareTemplateTagValueNode(
            $node->name,
            $node->bound,
            $node->description,
            $docContent
        );

        $startAndEnd = $node->getAttribute(PhpDocAttributeKey::START_AND_END);
        $spacingAwareTemplateTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);

        return $spacingAwareTemplateTagValueNode;
    }
}

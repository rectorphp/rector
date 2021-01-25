<?php

declare(strict_types=1);

namespace Rector\Core\Exclusion;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @todo move to symplify/skipper if grown enough
 * @see \Rector\Core\Tests\Exclusion\ExclusionManagerTest
 */
final class ExclusionManager
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function isNodeSkippedByRector(Node $node, PhpRectorInterface $phpRector): bool
    {
        if ($node instanceof PropertyProperty || $node instanceof Const_) {
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (! $node instanceof Node) {
                return false;
            }
        }

        if ($this->hasNoRectorPhpDocTagMatch($node, $phpRector)) {
            return true;
        }

        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $node instanceof Stmt && $parentNode !== null) {
            return $this->isNodeSkippedByRector($parentNode, $phpRector);
        }

        return false;
    }

    private function hasNoRectorPhpDocTagMatch(Node $node, PhpRectorInterface $phpRector): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        /** @var PhpDocTagNode[] $noRectorTags */
        $noRectorTags = array_merge($phpDocInfo->getTagsByName('noRector'), $phpDocInfo->getTagsByName('norector'));
        $rectorClass = get_class($phpRector);

        foreach ($noRectorTags as $noRectorTag) {
            if (! $noRectorTag->value instanceof GenericTagValueNode) {
                throw new ShouldNotHappenException();
            }

            $description = $noRectorTag->value->value;
            if ($description === '') {
                return true;
            }

            $description = ltrim($description, '\\');
            if ($description === $rectorClass) {
                return true;
            }

            if (! is_a($description, RectorInterface::class, true)) {
                return true;
            }
        }

        return false;
    }
}

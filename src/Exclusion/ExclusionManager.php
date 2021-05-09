<?php

declare (strict_types=1);
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
 * @see \Rector\Core\Tests\Exclusion\ExclusionManagerTest
 */
final class ExclusionManager
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function isNodeSkippedByRector(\PhpParser\Node $node, \Rector\Core\Contract\Rector\PhpRectorInterface $phpRector) : bool
    {
        if ($node instanceof \PhpParser\Node\Stmt\PropertyProperty || $node instanceof \PhpParser\Node\Const_) {
            $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        }
        if ($this->hasNoRectorPhpDocTagMatch($node, $phpRector)) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Stmt) {
            return \false;
        }
        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return \false;
        }
        return $this->isNodeSkippedByRector($parentNode, $phpRector);
    }
    private function hasNoRectorPhpDocTagMatch(\PhpParser\Node $node, \Rector\Core\Contract\Rector\PhpRectorInterface $phpRector) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        /** @var PhpDocTagNode[] $noRectorTags */
        $noRectorTags = \array_merge($phpDocInfo->getTagsByName('noRector'), $phpDocInfo->getTagsByName('norector'));
        $rectorClass = \get_class($phpRector);
        foreach ($noRectorTags as $noRectorTag) {
            if (!$noRectorTag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $description = $noRectorTag->value->value;
            if ($description === '') {
                return \true;
            }
            $description = \ltrim($description, '\\');
            if ($description === $rectorClass) {
                return \true;
            }
            if (!\is_a($description, \Rector\Core\Contract\Rector\RectorInterface::class, \true)) {
                return \true;
            }
        }
        return \false;
    }
}

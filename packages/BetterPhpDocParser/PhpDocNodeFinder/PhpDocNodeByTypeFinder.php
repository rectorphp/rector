<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeFinder;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
/**
 * @template TNode as \PHPStan\PhpDocParser\Ast\Node
 */
final class PhpDocNodeByTypeFinder
{
    /**
     * @param class-string<TNode> $desiredType
     * @return array<TNode>
     */
    public function findByType(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, string $desiredType) : array
    {
        $phpDocNodeTraverser = new \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $foundNodes = [];
        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function ($node) use(&$foundNodes, $desiredType) {
            if (!\is_a($node, $desiredType, \true)) {
                return $node;
            }
            /** @var TNode $node */
            $foundNodes[] = $node;
            return $node;
        });
        return $foundNodes;
    }
    /**
     * @param class-string[] $classes
     * @return DoctrineAnnotationTagValueNode[]
     */
    public function findDoctrineAnnotationsByClasses(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, array $classes) : array
    {
        $doctrineAnnotationTagValueNodes = [];
        foreach ($classes as $class) {
            $justFoundTagValueNodes = $this->findDoctrineAnnotationsByClass($phpDocNode, $class);
            $doctrineAnnotationTagValueNodes = \array_merge($doctrineAnnotationTagValueNodes, $justFoundTagValueNodes);
        }
        return $doctrineAnnotationTagValueNodes;
    }
    /**
     * @param class-string $desiredClass
     * @return DoctrineAnnotationTagValueNode[]
     */
    public function findDoctrineAnnotationsByClass(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, string $desiredClass) : array
    {
        $desiredDoctrineTagValueNodes = [];
        /** @var DoctrineAnnotationTagValueNode[] $doctrineTagValueNodes */
        $doctrineTagValueNodes = $this->findByType($phpDocNode, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode::class);
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            if ($doctrineTagValueNode->hasClassName($desiredClass)) {
                $desiredDoctrineTagValueNodes[] = $doctrineTagValueNode;
            }
        }
        return $desiredDoctrineTagValueNodes;
    }
}

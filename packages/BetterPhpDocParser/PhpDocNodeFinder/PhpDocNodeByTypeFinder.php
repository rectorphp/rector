<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeFinder;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
/**
 * @template TNode as \PHPStan\PhpDocParser\Ast\Node
 */
final class PhpDocNodeByTypeFinder
{
    /**
     * @param class-string<TNode> $desiredType
     * @return array<TNode>
     */
    public function findByType(PhpDocNode $phpDocNode, string $desiredType) : array
    {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $foundNodes = [];
        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', static function (Node $node) use(&$foundNodes, $desiredType) : Node {
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
    public function findDoctrineAnnotationsByClasses(PhpDocNode $phpDocNode, array $classes) : array
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
    public function findDoctrineAnnotationsByClass(PhpDocNode $phpDocNode, string $desiredClass) : array
    {
        $desiredDoctrineTagValueNodes = [];
        /** @var DoctrineAnnotationTagValueNode[] $doctrineTagValueNodes */
        $doctrineTagValueNodes = $this->findByType($phpDocNode, DoctrineAnnotationTagValueNode::class);
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            if ($doctrineTagValueNode->hasClassName($desiredClass)) {
                $desiredDoctrineTagValueNodes[] = $doctrineTagValueNode;
            }
        }
        return $desiredDoctrineTagValueNodes;
    }
}

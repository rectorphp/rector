<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class PhpDocTagRemover
{
    public function removeByName(PhpDocInfo $phpDocInfo, string $name) : void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if ($this->areAnnotationNamesEqual($name, $phpDocChildNode->name)) {
                unset($phpDocNode->children[$key]);
                $phpDocInfo->markAsChanged();
            }
            if ($phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode && $phpDocChildNode->value->hasClassName($name)) {
                unset($phpDocNode->children[$key]);
                $phpDocInfo->markAsChanged();
            }
        }
    }
    public function removeTagValueFromNode(PhpDocInfo $phpDocInfo, Node $desiredNode) : void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function ($node) use($desiredNode, $phpDocInfo) : ?int {
            if ($node instanceof PhpDocTagNode && $node->value === $desiredNode) {
                $phpDocInfo->markAsChanged();
                return PhpDocNodeTraverser::NODE_REMOVE;
            }
            if ($node !== $desiredNode) {
                return null;
            }
            $phpDocInfo->markAsChanged();
            return PhpDocNodeTraverser::NODE_REMOVE;
        });
    }
    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName) : bool
    {
        $firstAnnotationName = \trim($firstAnnotationName, '@');
        $secondAnnotationName = \trim($secondAnnotationName, '@');
        return $firstAnnotationName === $secondAnnotationName;
    }
}

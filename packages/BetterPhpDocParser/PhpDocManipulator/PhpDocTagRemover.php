<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class PhpDocTagRemover
{
    public function removeByName(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, string $name) : void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (!$phpDocChildNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                continue;
            }
            if ($this->areAnnotationNamesEqual($name, $phpDocChildNode->name)) {
                unset($phpDocNode->children[$key]);
                $phpDocInfo->markAsChanged();
            }
            if ($phpDocChildNode->value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                $doctrineAnnotationTagValueNode = $phpDocChildNode->value;
                if ($doctrineAnnotationTagValueNode->hasClassName($name)) {
                    unset($phpDocNode->children[$key]);
                    $phpDocInfo->markAsChanged();
                }
            }
        }
    }
    public function removeTagValueFromNode(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\PhpDocParser\Ast\Node $desiredNode) : void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $phpDocNodeTraverser = new \RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', function ($node) use($desiredNode, $phpDocInfo) : ?int {
            if ($node instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode && $node->value === $desiredNode) {
                $phpDocInfo->markAsChanged();
                return \RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
            }
            if ($node !== $desiredNode) {
                return null;
            }
            $phpDocInfo->markAsChanged();
            return \RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
        });
    }
    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName) : bool
    {
        $firstAnnotationName = \trim($firstAnnotationName, '@');
        $secondAnnotationName = \trim($secondAnnotationName, '@');
        return $firstAnnotationName === $secondAnnotationName;
    }
}

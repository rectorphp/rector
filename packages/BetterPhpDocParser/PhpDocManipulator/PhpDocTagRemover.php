<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
final class PhpDocTagRemover
{
    public function removeByName(PhpDocInfo $phpDocInfo, string $name) : bool
    {
        $hasChanged = \false;
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if ($this->areAnnotationNamesEqual($name, $phpDocChildNode->name)) {
                unset($phpDocNode->children[$key]);
                $hasChanged = \true;
            }
            if ($phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode && $phpDocChildNode->value->hasClassName($name)) {
                unset($phpDocNode->children[$key]);
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
    public function removeTagValueFromNode(PhpDocInfo $phpDocInfo, Node $desiredNode) : bool
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $hasChanged = \false;
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocNode, '', static function (Node $node) use($desiredNode, &$hasChanged) : ?int {
            if ($node instanceof PhpDocTagNode && $node->value === $desiredNode) {
                $hasChanged = \true;
                return PhpDocNodeTraverser::NODE_REMOVE;
            }
            if ($node !== $desiredNode) {
                return null;
            }
            $hasChanged = \true;
            return PhpDocNodeTraverser::NODE_REMOVE;
        });
        return $hasChanged;
    }
    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName) : bool
    {
        $firstAnnotationName = \trim($firstAnnotationName, '@');
        $secondAnnotationName = \trim($secondAnnotationName, '@');
        return $firstAnnotationName === $secondAnnotationName;
    }
}

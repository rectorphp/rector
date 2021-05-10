<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
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
                $tagClass = $phpDocChildNode->value->getAnnotationClass();
                if ($tagClass === $name) {
                    unset($phpDocNode->children[$key]);
                    $phpDocInfo->markAsChanged();
                }
            }
        }
    }
    public function removeTagValueFromNode(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\PhpDocParser\Ast\Node $desiredNode) : void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if ($phpDocChildNode === $desiredNode) {
                unset($phpDocNode->children[$key]);
                $phpDocInfo->markAsChanged();
                continue;
            }
            if (!$phpDocChildNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                continue;
            }
            if ($phpDocChildNode->value !== $desiredNode) {
                continue;
            }
            unset($phpDocNode->children[$key]);
            $phpDocInfo->markAsChanged();
        }
    }
    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName) : bool
    {
        $firstAnnotationName = \trim($firstAnnotationName, '@');
        $secondAnnotationName = \trim($secondAnnotationName, '@');
        return $firstAnnotationName === $secondAnnotationName;
    }
}

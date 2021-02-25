<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocTagRemover
{
    public function removeByName(PhpDocInfo $phpDocInfo, string $name): void
    {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        foreach ($attributeAwarePhpDocNode->children as $key => $child) {
            if (! $child instanceof PhpDocTagNode) {
                continue;
            }

            if (! $this->areAnnotationNamesEqual($name, $child->name)) {
                continue;
            }

            unset($attributeAwarePhpDocNode->children[$key]);

            $phpDocInfo->markAsChanged();
        }
    }

    public function removeTagValueFromNode(PhpDocInfo $phpDocInfo, Node $desiredNode): void
    {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        foreach ($attributeAwarePhpDocNode->children as $key => $child) {
            if ($child === $desiredNode) {
                unset($attributeAwarePhpDocNode->children[$key]);
                $phpDocInfo->markAsChanged();
                continue;
            }

            if (! $child instanceof PhpDocTagNode) {
                continue;
            }

            if ($child->value !== $desiredNode) {
                continue;
            }

            unset($attributeAwarePhpDocNode->children[$key]);
            $phpDocInfo->markAsChanged();
        }
    }

    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName): bool
    {
        $firstAnnotationName = trim($firstAnnotationName, '@');
        $secondAnnotationName = trim($secondAnnotationName, '@');

        return $firstAnnotationName === $secondAnnotationName;
    }
}

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

        foreach ($attributeAwarePhpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $this->areAnnotationNamesEqual($name, $phpDocChildNode->name)) {
                continue;
            }

            unset($attributeAwarePhpDocNode->children[$key]);

            $phpDocInfo->markAsChanged();
        }
    }

    public function removeTagValueFromNode(PhpDocInfo $phpDocInfo, Node $desiredNode): void
    {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        foreach ($attributeAwarePhpDocNode->children as $key => $phpDocChildNode) {
            if ($phpDocChildNode === $desiredNode) {
                unset($attributeAwarePhpDocNode->children[$key]);
                $phpDocInfo->markAsChanged();
                continue;
            }

            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value !== $desiredNode) {
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

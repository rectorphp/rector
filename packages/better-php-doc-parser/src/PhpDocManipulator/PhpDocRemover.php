<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocRemover
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
        }
    }

    public function removeTagValueFromNode(PhpDocInfo $phpDocInfo, PhpDocTagValueNode $phpDocTagValueNode): void
    {
        $attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        foreach ($attributeAwarePhpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value !== $phpDocTagValueNode) {
                continue;
            }

            unset($attributeAwarePhpDocNode->children[$key]);
        }
    }

    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName): bool
    {
        $firstAnnotationName = trim($firstAnnotationName, '@');
        $secondAnnotationName = trim($secondAnnotationName, '@');

        return $firstAnnotationName === $secondAnnotationName;
    }
}

<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Annotation\StaticAnnotationNaming;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class DocBlockTagReplacer
{
    public function replaceTagByAnother(PhpDocInfo $phpDocInfo, string $oldTag, string $newTag): void
    {
        $oldTag = StaticAnnotationNaming::normalizeName($oldTag);
        $newTag = StaticAnnotationNaming::normalizeName($newTag);

        foreach ($phpDocInfo->findAllByType(PhpDocTagNode::class) as $phpDocChildNode) {
            if ($phpDocChildNode->name !== $oldTag) {
                continue;
            }

            $phpDocChildNode->name = $newTag;
            $phpDocInfo->markAsChanged();
        }
    }
}

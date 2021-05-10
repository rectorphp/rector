<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class DocBlockTagReplacer
{
    public function __construct(
        private AnnotationNaming $annotationNaming
    ) {
    }

    public function replaceTagByAnother(PhpDocInfo $phpDocInfo, string $oldTag, string $newTag): void
    {
        $oldTag = $this->annotationNaming->normalizeName($oldTag);
        $newTag = $this->annotationNaming->normalizeName($newTag);

        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->name !== $oldTag) {
                continue;
            }

            unset($phpDocNode->children[$key]);
            $phpDocNode->children[] = new PhpDocTagNode($newTag, new GenericTagValueNode(''));
        }
    }
}

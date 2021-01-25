<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class DocBlockTagReplacer
{
    /**
     * @var AnnotationNaming
     */
    private $annotationNaming;

    public function __construct(AnnotationNaming $annotationNaming)
    {
        $this->annotationNaming = $annotationNaming;
    }

    public function replaceTagByAnother(PhpDocInfo $phpDocInfo, string $oldTag, string $newTag): void
    {
        $oldTag = $this->annotationNaming->normalizeName($oldTag);
        $newTag = $this->annotationNaming->normalizeName($newTag);

        /** @var PhpDocTagNode[] $phpDocTagNodes */
        $phpDocTagNodes = $phpDocInfo->findAllByType(PhpDocTagNode::class);

        foreach ($phpDocTagNodes as $phpDocTagNode) {
            if ($phpDocTagNode->name !== $oldTag) {
                continue;
            }

            $phpDocTagNode->name = $newTag;
            $phpDocInfo->markAsChanged();
        }
    }
}

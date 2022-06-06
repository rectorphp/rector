<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
final class DocBlockTagReplacer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Annotation\AnnotationNaming
     */
    private $annotationNaming;
    public function __construct(AnnotationNaming $annotationNaming)
    {
        $this->annotationNaming = $annotationNaming;
    }
    public function replaceTagByAnother(PhpDocInfo $phpDocInfo, string $oldTag, string $newTag) : bool
    {
        $hasChanged = \false;
        $oldTag = $this->annotationNaming->normalizeName($oldTag);
        $newTag = $this->annotationNaming->normalizeName($newTag);
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if ($phpDocChildNode->name !== $oldTag) {
                continue;
            }
            unset($phpDocNode->children[$key]);
            $phpDocNode->children[] = new PhpDocTagNode($newTag, new GenericTagValueNode(''));
            $hasChanged = \true;
        }
        return $hasChanged;
    }
}

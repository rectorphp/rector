<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\ValueObject;

use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
final class DoctrineTagAndAnnotationToAttribute
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
     */
    private $doctrineAnnotationTagValueNode;
    /**
     * @readonly
     * @var \Rector\Php80\ValueObject\AnnotationToAttribute
     */
    private $annotationToAttribute;
    public function __construct(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, AnnotationToAttribute $annotationToAttribute)
    {
        $this->doctrineAnnotationTagValueNode = $doctrineAnnotationTagValueNode;
        $this->annotationToAttribute = $annotationToAttribute;
    }
    public function getDoctrineAnnotationTagValueNode() : DoctrineAnnotationTagValueNode
    {
        return $this->doctrineAnnotationTagValueNode;
    }
    public function getAnnotationToAttribute() : AnnotationToAttribute
    {
        return $this->annotationToAttribute;
    }
}

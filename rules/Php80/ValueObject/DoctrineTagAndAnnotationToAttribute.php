<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
final class DoctrineTagAndAnnotationToAttribute
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
     */
    private $doctrineAnnotationTagValueNode;
    /**
     * @var \Rector\Php80\ValueObject\AnnotationToAttribute
     */
    private $annotationToAttribute;
    public function __construct(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute)
    {
        $this->doctrineAnnotationTagValueNode = $doctrineAnnotationTagValueNode;
        $this->annotationToAttribute = $annotationToAttribute;
    }
    public function getDoctrineAnnotationTagValueNode() : \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        return $this->doctrineAnnotationTagValueNode;
    }
    public function getAnnotationToAttribute() : \Rector\Php80\ValueObject\AnnotationToAttribute
    {
        return $this->annotationToAttribute;
    }
}

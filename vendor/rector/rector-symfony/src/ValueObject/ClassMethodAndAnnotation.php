<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
final class ClassMethodAndAnnotation
{
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
     */
    private $doctrineAnnotationTagValueNode;
    public function __construct(string $methodName, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $this->methodName = $methodName;
        $this->doctrineAnnotationTagValueNode = $doctrineAnnotationTagValueNode;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getDoctrineAnnotationTagValueNode() : DoctrineAnnotationTagValueNode
    {
        return $this->doctrineAnnotationTagValueNode;
    }
}

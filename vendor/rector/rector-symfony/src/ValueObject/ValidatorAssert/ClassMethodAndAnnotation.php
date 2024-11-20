<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\ValidatorAssert;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
final class ClassMethodAndAnnotation
{
    /**
     * @var string[]
     * @readonly
     */
    private array $possibleMethodNames;
    /**
     * @readonly
     */
    private DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode;
    /**
     * @param string[] $possibleMethodNames
     */
    public function __construct(array $possibleMethodNames, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $this->possibleMethodNames = $possibleMethodNames;
        $this->doctrineAnnotationTagValueNode = $doctrineAnnotationTagValueNode;
    }
    /**
     * @return string[]
     */
    public function getPossibleMethodNames() : array
    {
        return $this->possibleMethodNames;
    }
    public function getDoctrineAnnotationTagValueNode() : DoctrineAnnotationTagValueNode
    {
        return $this->doctrineAnnotationTagValueNode;
    }
}

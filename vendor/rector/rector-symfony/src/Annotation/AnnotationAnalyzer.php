<?php

declare (strict_types=1);
namespace Rector\Symfony\Annotation;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Symfony\Enum\SymfonyAnnotation;
final class AnnotationAnalyzer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function hasClassMethodWithTemplateAnnotation(Class_ $class) : bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $templateDoctrineAnnotationTagValueNode = $this->getDoctrineAnnotationTagValueNode($classMethod, SymfonyAnnotation::TEMPLATE);
            if ($templateDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                return \true;
            }
        }
        return \false;
    }
    public function getDoctrineAnnotationTagValueNode(ClassMethod $classMethod, string $annotationClass) : ?DoctrineAnnotationTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        return $phpDocInfo->getByAnnotationClass($annotationClass);
    }
}

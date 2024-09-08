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
        $classTemplateAnnotation = $this->getDoctrineAnnotationTagValueNode($class, SymfonyAnnotation::TEMPLATE);
        if ($classTemplateAnnotation instanceof DoctrineAnnotationTagValueNode) {
            return \true;
        }
        foreach ($class->getMethods() as $classMethod) {
            $classMethodTemplateAnnotation = $this->getDoctrineAnnotationTagValueNode($classMethod, SymfonyAnnotation::TEMPLATE);
            if ($classMethodTemplateAnnotation instanceof DoctrineAnnotationTagValueNode) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod $node
     */
    public function getDoctrineAnnotationTagValueNode($node, string $annotationClass) : ?DoctrineAnnotationTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        return $phpDocInfo->getByAnnotationClass($annotationClass);
    }
}

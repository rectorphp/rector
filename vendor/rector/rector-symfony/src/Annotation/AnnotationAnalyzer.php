<?php

declare (strict_types=1);
namespace Rector\Symfony\Annotation;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Symfony\Enum\SymfonyAnnotation;
final class AnnotationAnalyzer
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private AttrinationFinder $attrinationFinder;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, AttrinationFinder $attrinationFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->attrinationFinder = $attrinationFinder;
    }
    public function hasClassMethodWithTemplateAnnotation(Class_ $class) : bool
    {
        if ($this->attrinationFinder->hasByOne($class, SymfonyAnnotation::TEMPLATE)) {
            return \true;
        }
        foreach ($class->getMethods() as $classMethod) {
            if ($this->attrinationFinder->hasByOne($classMethod, SymfonyAnnotation::TEMPLATE)) {
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

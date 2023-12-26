<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AnnotationTransformer;

use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\CodeQuality\Contract\ClassAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\Contract\PropertyAnnotationTransformerInterface;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
final class YamlToAnnotationTransformer
{
    /**
     * @var ClassAnnotationTransformerInterface[]
     * @readonly
     */
    private $classAnnotationTransformers;
    /**
     * @var PropertyAnnotationTransformerInterface[]
     * @readonly
     */
    private $propertyAnnotationTransformers;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @param ClassAnnotationTransformerInterface[] $classAnnotationTransformers
     * @param PropertyAnnotationTransformerInterface[] $propertyAnnotationTransformers
     */
    public function __construct(iterable $classAnnotationTransformers, iterable $propertyAnnotationTransformers, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->classAnnotationTransformers = $classAnnotationTransformers;
        $this->propertyAnnotationTransformers = $propertyAnnotationTransformers;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function transform(Class_ $class, EntityMapping $entityMapping) : void
    {
        $this->transformClass($class, $entityMapping);
        $this->transformProperties($class, $entityMapping);
    }
    private function transformClass(Class_ $class, EntityMapping $entityMapping) : void
    {
        $classPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        foreach ($this->classAnnotationTransformers as $classAnnotationTransformer) {
            $classAnnotationTransformer->transform($entityMapping, $classPhpDocInfo);
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($class);
    }
    private function transformProperties(Class_ $class, EntityMapping $entityMapping) : void
    {
        foreach ($class->getProperties() as $property) {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            foreach ($this->propertyAnnotationTransformers as $propertyAnnotationTransformer) {
                $propertyAnnotationTransformer->transform($entityMapping, $propertyPhpDocInfo, $property);
            }
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
        }
    }
}

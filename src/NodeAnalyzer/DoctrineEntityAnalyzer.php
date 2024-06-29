<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
/**
 * @api used in doctrine
 */
final class DoctrineEntityAnalyzer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var string[]
     */
    private const DOCTRINE_MAPPING_CLASSES = ['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Embeddable', 'Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Document', 'Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\EmbeddedDocument'];
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function hasClassAnnotation(Class_ $class) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($class);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        return $phpDocInfo->hasByAnnotationClasses(self::DOCTRINE_MAPPING_CLASSES);
    }
    public function hasClassReflectionAttribute(ClassReflection $classReflection) : bool
    {
        /** @var ReflectionClass $nativeReflectionClass */
        $nativeReflectionClass = $classReflection->getNativeReflection();
        // skip early in case of no attributes at all
        if ((\method_exists($nativeReflectionClass, 'getAttributes') ? $nativeReflectionClass->getAttributes() : []) === []) {
            return \false;
        }
        foreach (self::DOCTRINE_MAPPING_CLASSES as $doctrineMappingClass) {
            // skip entities
            if ((\method_exists($nativeReflectionClass, 'getAttributes') ? $nativeReflectionClass->getAttributes($doctrineMappingClass) : []) !== []) {
                return \true;
            }
        }
        return \false;
    }
}

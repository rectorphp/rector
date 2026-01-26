<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ReflectionProvider;
final class DoctrineEntityDocumentAnalyser
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string[]
     */
    private const ENTITY_DOCBLOCK_MARKERS = ['@Document', '@ORM\Document', '@Entity', '@ORM\Entity'];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isEntityClass(string $className): bool
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return \false;
        }
        foreach (self::ENTITY_DOCBLOCK_MARKERS as $entityDocBlockMarkers) {
            if (strpos($resolvedPhpDocBlock->getPhpDocString(), $entityDocBlockMarkers) !== \false) {
                return \true;
            }
        }
        // @todo apply attributes as well
        return \false;
    }
}

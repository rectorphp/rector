<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeAnalyzer\DoctrineEntityAnalyzer;
final class DoctrineEntityDetector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\DoctrineEntityAnalyzer
     */
    private $doctrineEntityAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(DoctrineEntityAnalyzer $doctrineEntityAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->doctrineEntityAnalyzer = $doctrineEntityAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function detect(Class_ $class) : bool
    {
        // A. check annotations
        if ($this->doctrineEntityAnalyzer->hasClassAnnotation($class)) {
            return \true;
        }
        if (!$class->namespacedName instanceof Name) {
            return \false;
        }
        $className = $class->namespacedName->toString();
        // B. check attributes
        $classReflection = $this->reflectionProvider->getClass($className);
        return $this->doctrineEntityAnalyzer->hasClassReflectionAttribute($classReflection);
    }
}

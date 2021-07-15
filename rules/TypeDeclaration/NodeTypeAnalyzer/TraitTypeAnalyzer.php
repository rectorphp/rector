<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class TraitTypeAnalyzer
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isTraitType(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        $fullyQualifiedName = $this->nodeTypeResolver->getFullyQualifiedClassName($type);
        if (!$this->reflectionProvider->hasClass($fullyQualifiedName)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedName);
        return $classReflection->isTrait();
    }
}

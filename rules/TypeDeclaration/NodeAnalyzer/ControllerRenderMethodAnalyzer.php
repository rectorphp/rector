<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use RectorPrefix20211227\Symplify\Astral\Naming\SimpleNameResolver;
final class ControllerRenderMethodAnalyzer
{
    /**
     * @readonly
     * @var \Symplify\Astral\Naming\SimpleNameResolver
     */
    private $simpleNameResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(\RectorPrefix20211227\Symplify\Astral\Naming\SimpleNameResolver $simpleNameResolver, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->simpleNameResolver = $simpleNameResolver;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function isRenderMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Analyser\Scope $scope) : bool
    {
        // nette one?
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        if ($this->isNetteRenderMethod($classReflection, $classMethod)) {
            return \true;
        }
        return $this->isSymfonyRenderMethod($classReflection, $classMethod);
    }
    private function isNetteRenderMethod(\PHPStan\Reflection\ClassReflection $classReflection, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$classReflection->isSubclassOf('Nette\\Application\\UI\\Control')) {
            return \false;
        }
        if (!$classMethod->isPublic()) {
            return \false;
        }
        return $this->simpleNameResolver->isNames($classMethod->name, ['render*', 'handle*', 'action*']);
    }
    private function isSymfonyRenderMethod(\PHPStan\Reflection\ClassReflection $classReflection, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller') && !$classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController')) {
            return \false;
        }
        if (!$classMethod->isPublic()) {
            return \false;
        }
        if ($this->simpleNameResolver->isNames($classMethod->name, ['__invoke', '*render'])) {
            return \true;
        }
        return $this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Symfony\\Component\\Routing\\Annotation\\Route');
    }
}

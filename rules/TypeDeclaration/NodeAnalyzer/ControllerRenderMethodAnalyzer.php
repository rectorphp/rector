<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
final class ControllerRenderMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, ReflectionResolver $reflectionResolver)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @api
     */
    public function isRenderMethod(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isSymfonyRenderMethod($classReflection, $classMethod);
    }
    private function isSymfonyRenderMethod(ClassReflection $classReflection, ClassMethod $classMethod) : bool
    {
        if (!$classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller') && !$classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController')) {
            return \false;
        }
        if (!$classMethod->isPublic()) {
            return \false;
        }
        $classMethodName = $classMethod->name->toString();
        if ($classMethodName === MethodName::INVOKE) {
            return \true;
        }
        if (\substr_compare($classMethodName, 'action', -\strlen('action')) === 0) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'Symfony\\Component\\Routing\\Annotation\\Route')) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        return $phpDocInfo->hasByAnnotationClass('Symfony\\Component\\Routing\\Annotation\\Route');
    }
}

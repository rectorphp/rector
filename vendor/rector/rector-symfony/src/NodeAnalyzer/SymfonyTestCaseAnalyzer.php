<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Reflection\ReflectionResolver;
final class SymfonyTestCaseAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isInWebTestCase(\PhpParser\Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        return $classReflection->isSubclassOf('Symfony\\Bundle\\FrameworkBundle\\Test\\WebTestCase');
    }
}

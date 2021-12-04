<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class PHPUnitTypeDeclarationDecorator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->astResolver = $astResolver;
    }
    public function decorate(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        // skip test run
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }
        $setUpClassMethod = $this->astResolver->resolveClassMethod('PHPUnit\\Framework\\TestCase', \Rector\Core\ValueObject\MethodName::SET_UP);
        if (!$setUpClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        $classMethod->returnType = $setUpClassMethod->returnType;
    }
}

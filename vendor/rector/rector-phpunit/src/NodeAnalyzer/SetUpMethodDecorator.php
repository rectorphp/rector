<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\Core\PhpParser\AstResolver;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
/**
 * Decorate setUp() and tearDown() with "void" when local TestClass class uses them
 */
final class SetUpMethodDecorator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(AstResolver $astResolver)
    {
        $this->astResolver = $astResolver;
    }
    public function decorate(ClassMethod $classMethod) : void
    {
        // skip test run
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }
        $setUpClassMethod = $this->astResolver->resolveClassMethod('PHPUnit\\Framework\\TestCase', MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return;
        }
        $classMethod->returnType = $setUpClassMethod->returnType;
    }
}

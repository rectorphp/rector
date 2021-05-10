<?php

declare (strict_types=1);
namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\CodingStyle\Naming\ClassNaming;
use RectorPrefix20210510\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
final class StaticMethodClassFactory
{
    /**
     * @var ClassMethodFactory
     */
    private $classMethodFactory;
    /**
     * @var ClassNaming
     */
    private $classNaming;
    public function __construct(\Rector\Transform\NodeFactory\ClassMethodFactory $classMethodFactory, ClassNaming $classNaming)
    {
        $this->classMethodFactory = $classMethodFactory;
        $this->classNaming = $classNaming;
    }
    /**
     * @param Function_[] $functions
     */
    public function createStaticMethodClass(string $shortClassName, array $functions) : Class_
    {
        $classBuilder = new ClassBuilder($shortClassName);
        $classBuilder->makeFinal();
        foreach ($functions as $function) {
            $staticClassMethod = $this->createStaticMethod($function);
            $classBuilder->addStmt($staticClassMethod);
        }
        return $classBuilder->getNode();
    }
    private function createStaticMethod(Function_ $function) : ClassMethod
    {
        $methodName = $this->classNaming->createMethodNameFromFunction($function);
        return $this->classMethodFactory->createClassMethodFromFunction($methodName, $function);
    }
}

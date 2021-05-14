<?php

declare (strict_types=1);
namespace Rector\Transform\NodeFactory;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\CodingStyle\Naming\ClassNaming;
use RectorPrefix20210514\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder;
final class StaticMethodClassFactory
{
    /**
     * @var \Rector\Transform\NodeFactory\ClassMethodFactory
     */
    private $classMethodFactory;
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    public function __construct(\Rector\Transform\NodeFactory\ClassMethodFactory $classMethodFactory, \Rector\CodingStyle\Naming\ClassNaming $classNaming)
    {
        $this->classMethodFactory = $classMethodFactory;
        $this->classNaming = $classNaming;
    }
    /**
     * @param Function_[] $functions
     */
    public function createStaticMethodClass(string $shortClassName, array $functions) : \PhpParser\Node\Stmt\Class_
    {
        $classBuilder = new \RectorPrefix20210514\Symplify\Astral\ValueObject\NodeBuilder\ClassBuilder($shortClassName);
        $classBuilder->makeFinal();
        foreach ($functions as $function) {
            $staticClassMethod = $this->createStaticMethod($function);
            $classBuilder->addStmt($staticClassMethod);
        }
        return $classBuilder->getNode();
    }
    private function createStaticMethod(\PhpParser\Node\Stmt\Function_ $function) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodName = $this->classNaming->createMethodNameFromFunction($function);
        return $this->classMethodFactory->createClassMethodFromFunction($methodName, $function);
    }
}

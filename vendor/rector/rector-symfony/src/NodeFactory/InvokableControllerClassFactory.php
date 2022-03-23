<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
final class InvokableControllerClassFactory
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\InvokableControllerNameFactory
     */
    private $invokableControllerNameFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Symfony\NodeFactory\InvokableControllerNameFactory $invokableControllerNameFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->invokableControllerNameFactory = $invokableControllerNameFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function createWithActionClassMethod(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $actionClassMethod) : \PhpParser\Node\Stmt\Class_
    {
        $controllerName = $this->createControllerName($class, $actionClassMethod);
        $actionClassMethod->name = new \PhpParser\Node\Identifier(\Rector\Core\ValueObject\MethodName::INVOKE);
        $newClass = clone $class;
        $newClassStmts = [];
        foreach ($class->stmts as $classStmt) {
            if (!$classStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
                $newClassStmts[] = $classStmt;
                continue;
            }
            // avoid duplicated names
            if ($classStmt->isMagic() && !$this->nodeNameResolver->isName($classStmt->name, \Rector\Core\ValueObject\MethodName::INVOKE)) {
                $newClassStmts[] = $classStmt;
                continue;
            }
            if (!$classStmt->isPublic()) {
                $newClassStmts[] = $classStmt;
            }
        }
        $newClassStmts[] = $actionClassMethod;
        $newClass->name = new \PhpParser\Node\Identifier($controllerName);
        $newClass->stmts = $newClassStmts;
        return $newClass;
    }
    private function createControllerName(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $actionClassMethod) : string
    {
        /** @var Identifier $className */
        $className = $class->name;
        return $this->invokableControllerNameFactory->createControllerName($className, $actionClassMethod->name->toString());
    }
}

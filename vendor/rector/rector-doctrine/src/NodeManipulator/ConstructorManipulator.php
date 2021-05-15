<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ConstructorManipulator
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator)
    {
        $this->nodeFactory = $nodeFactory;
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function addStmtToConstructor(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Expression $newExpression) : void
    {
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $constructClassMethod->stmts[] = $newExpression;
        } else {
            $constructClassMethod = $this->nodeFactory->createPublicMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
            $constructClassMethod->stmts[] = $newExpression;
            $this->classInsertManipulator->addAsFirstMethod($class, $constructClassMethod);
            $class->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        }
    }
}

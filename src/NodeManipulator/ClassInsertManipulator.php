<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\NodeFactory;
final class ClassInsertManipulator
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @api
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\ClassMethod $addedStmt
     */
    public function addAsFirstMethod(Class_ $class, $addedStmt) : void
    {
        $scope = $class->getAttribute(AttributeKey::SCOPE);
        $addedStmt->setAttribute(AttributeKey::SCOPE, $scope);
        // no stmts? add this one
        if ($class->stmts === []) {
            $class->stmts[] = $addedStmt;
            return;
        }
        $newClassStmts = [];
        $isAdded = \false;
        foreach ($class->stmts as $key => $classStmt) {
            $nextStmt = $class->stmts[$key + 1] ?? null;
            if ($isAdded === \false) {
                // first class method
                if ($classStmt instanceof ClassMethod) {
                    $newClassStmts[] = $addedStmt;
                    $newClassStmts[] = $classStmt;
                    $isAdded = \true;
                    continue;
                }
                // after last property
                if ($classStmt instanceof Property && !$nextStmt instanceof Property) {
                    $newClassStmts[] = $classStmt;
                    $newClassStmts[] = $addedStmt;
                    $isAdded = \true;
                    continue;
                }
            }
            $newClassStmts[] = $classStmt;
        }
        // still not added? try after last trait
        // @todo
        if ($isAdded) {
            $class->stmts = $newClassStmts;
            return;
        }
        // keep added at least as first stmt
        $class->stmts = \array_merge([$addedStmt], $class->stmts);
    }
    /**
     * @internal Use PropertyAdder service instead
     */
    public function addPropertyToClass(Class_ $class, string $name, ?Type $type) : void
    {
        $existingProperty = $class->getProperty($name);
        if ($existingProperty instanceof Property) {
            return;
        }
        $property = $this->nodeFactory->createPrivatePropertyFromNameAndType($name, $type);
        $this->addAsFirstMethod($class, $property);
    }
}

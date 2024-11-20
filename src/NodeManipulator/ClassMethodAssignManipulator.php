<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\NodeFactory;
final class ClassMethodAssignManipulator
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @var array<int, string[]>
     */
    private array $alreadyAddedClassMethodNames = [];
    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function addParameterAndAssignToMethod(ClassMethod $classMethod, string $name, ?Type $type, Assign $assign) : void
    {
        if ($this->hasMethodParameter($classMethod, $name)) {
            return;
        }
        $classMethod->params[] = $this->nodeFactory->createParamFromNameAndType($name, $type);
        $classMethod->stmts[] = new Expression($assign);
        $classMethodId = \spl_object_id($classMethod);
        $this->alreadyAddedClassMethodNames[$classMethodId][] = $name;
    }
    private function hasMethodParameter(ClassMethod $classMethod, string $name) : bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return \true;
            }
        }
        $classMethodId = \spl_object_id($classMethod);
        if (!isset($this->alreadyAddedClassMethodNames[$classMethodId])) {
            return \false;
        }
        return \in_array($name, $this->alreadyAddedClassMethodNames[$classMethodId], \true);
    }
}

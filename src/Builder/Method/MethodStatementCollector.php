<?php declare(strict_types=1);

namespace Rector\Builder\Method;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use SplObjectStorage;

final class MethodStatementCollector
{
    /**
     * @var Node[][]
     */
    private $methodStatements = [];

    public function __construct()
    {
        $this->methodStatements = new SplObjectStorage;
    }

    public function addStatementForMethod(ClassMethod $classMethod, Node $node): void
    {
        if (isset($this->methodStatements[$classMethod])) {
            $this->methodStatements[$classMethod][] = $node;
        } else {
            $this->methodStatements[$classMethod] = [$node];
        }
    }

    /**
     * @return Node[]
     */
    public function getStatementsForMethod(ClassMethod $classMethodNode): array
    {
        return $this->methodStatements[$classMethodNode] ?? [];
    }
}

<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\Method\MethodStatementCollector;
use Rector\Node\Attribute;

/**
 * Adds new statements to method.
 */
final class StatementToMethodAdder extends NodeVisitorAbstract
{
    /**
     * @var MethodStatementCollector
     */
    private $methodStatementCollector;

    public function __construct(MethodStatementCollector $methodStatementCollector)
    {
        $this->methodStatementCollector = $methodStatementCollector;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        foreach ($nodes as $key => $node) {
            if (! $node instanceof Class_) {
                continue;
            }

            foreach ($node->stmts as $id => $inClassStatement) {
                if ($inClassStatement instanceof ClassMethod) {
                    $node->stmts[$id] = $this->processClassMethod($inClassStatement);
                }
            }
        }

        return $nodes;
    }

    private function processClassMethod(ClassMethod $classMethodNode): ClassMethod
    {
        $methodStatements = $this->methodStatementCollector->getStatementsForMethod($classMethodNode);

        if (! count($methodStatements)) {
            return $classMethodNode;
        }

        foreach ($methodStatements as $methodStatement) {
            $classMethodNode->stmts[] = $methodStatement;
        }

        /** @var Node $parentNode */
        $parentNode = $classMethodNode->getAttribute('parentNode');
        $parentNode->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $classMethodNode;
    }
}

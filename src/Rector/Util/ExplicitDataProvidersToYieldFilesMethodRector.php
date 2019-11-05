<?php

declare(strict_types=1);

namespace Rector\Rector\Util;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

final class ExplicitDataProvidersToYieldFilesMethodRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        // TODO: Implement getDefinition() method.
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'provideData*')) {
            return null;
        }

        // should skip?
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        // return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
        $directory = new Node\Expr\BinaryOp\Concat(new Node\Scalar\MagicConst\Dir(), new Node\Scalar\String_(
            '/Fixture'
        ));
        $methodCall = $this->createMethodCall('this', 'yieldFilesFromDirectory', [$directory]);

        $return = new Return_($methodCall);
        $node->stmts = [$return];

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        foreach ($classMethod->getStmts() as $classMethodStmt) {
            if ($classMethodStmt instanceof Expression) {
                $classMethodStmt = $classMethodStmt->expr;
            }

            // not yield one
            if (! $classMethodStmt instanceof Node\Expr\Yield_) {
                return true;
            }

            if (! $classMethodStmt->value instanceof Node\Expr\Array_) {
                return true;
            }

            $array = $classMethodStmt->value;
            if (count($array->items) !== 1) {
                return true;
            }
        }

        return false;
    }
}
